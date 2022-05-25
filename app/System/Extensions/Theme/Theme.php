<?php

namespace App\System\Extensions\Theme;

use App\Http\Traits\App;
use App\System\Extensions\Theme\Support\Assets;
use App\System\Extensions\Theme\Support\Current;
use App\System\Extensions\Theme\Support\Module;
use App\System\Installer;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Theme {

    use App;

    private $app;
    public $manager;

    private $cacheDefaultPrefix = "theme_cache_default";
    private $cacheConfigurationPrefix = "theme_cache_configuration";
    private $cacheWaitingUpdatePrefix = "theme_waiting_ext_update";

    /**
     * Theme constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
        $this->manager = $this->app->make(Manager::class);
    }

    /**
     * @return mixed
     */
    public function start()
    {
        return $this->manager->load();
    }

    /**
     * @param $eid
     * @return mixed
     */
    public function enable($eid)
    {
        $this->app['theme.cache']->resetCache();

        if($this->checkCompatibility($eid))
            return $this->manager->setTheme("add", "$eid");

        return false;
    }

    /**
     * @return mixed
     */
    public function disable($eid = "")
    {
        $this->app['theme.cache']->resetCache();

        if($this->isDefault())
            return false;

        if($eid == "")
            return $this->manager->setTheme("remove", $this->getCurrent()->first()['id']);

        return $this->manager->setTheme("remove", $eid);
    }

    /**
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->manager->getCurrentTheme();
    }

    /**
     * @return mixed|null
     */
    public function current()
    {
        if($this->isDefault())
            return $this->app->make(Current::class, ['config' => null]);

        return $this->app->make(Current::class, ['config' => $this->manager->getConfig($this->getCurrent()->first()['id'])]);
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->getCurrent()->first() == "default" || $this->getCurrent()->isEmpty() ? true : false;
    }

    /**
     * @param $keys
     * @return array|mixed
     */
    public function config($keys)
    {
        if($this->isDefault()) {
            $path = resource_path('views/Config/config.json');

            if($this->app['cache']->has($this->cacheDefaultPrefix))
                return Arr::dot($this->app['cache']->get($this->cacheDefaultPrefix))[$keys];

            $data = json_decode(File::get($path), true)['config'];
            $this->app['cache']->forever($this->cacheDefaultPrefix, $data);

            if(empty(Arr::dot($this->app['cache']->get($this->cacheDefaultPrefix))[$keys]))
                return null;

            return Arr::dot($this->app['cache']->get($this->cacheDefaultPrefix))[$keys];
        }

        if(!$this->getConfigurationJson())
            return null;

        if(empty(Arr::dot($this->getConfigurationJson())[$keys]))
            return null;

        return Arr::dot($this->getConfigurationJson())[$keys];
    }

    /**
     * @param $keys
     * @return array
     */
    public function get($keys)
    {
        if($this->isDefault())
            return [];

        return $this->manager->getConfig($this->getCurrent()->first()['id'], $keys);
    }

    /**
     * @param string $eid
     * @return bool|mixed
     */
    public function getConfigurationJson($eid = "")
    {
        $path = extensions_path("Themes/" . $this->getFolderName($eid) . "/configuration.json");

        if(!$this->app['files']->exists($path))
            return false;

        if($this->app()->api()->isDeveloper())
            return json_decode($this->app['files']->get($path), true);

        if($this->app['cache']->has($this->cacheConfigurationPrefix))
            return $this->app['cache']->get($this->cacheConfigurationPrefix);

        $this->app['cache']->forever($this->cacheConfigurationPrefix, json_decode($this->app['files']->get($path), true));

        return $this->app['cache']->get($this->cacheConfigurationPrefix);
    }

    /**
     * @return bool|string
     */
    public function getFolderName($eid = "")
    {
        if($this->isDefault())
            return false;

        if($eid == "")
            return $this->get('slug');

        return $this->manager->getConfig($eid, 'slug');
    }

    /**
     * @return bool|string
     */
    public function __getFolderName($eid = "")
    {
        if($eid == "")
            return $this->get('slug');

        return $this->manager->getConfig($eid, 'slug');
    }

    /**
     * @return mixed
     */
    public function el()
    {
        return $this->app->make(Assets::class);
    }

    /**
     * @param $eid
     * @return mixed
     */
    public function if($eid)
    {
        return $this->app->make(Module::class, ["eid" => $eid]);
    }

    /**
     * @return mixed
     */
    public function getThemeList()
    {
        return $this->manager->getThemeList();
    }

    /**
     * @param $eid
     * @return bool|\Illuminate\Support\Collection|int
     */
    public function checkCompatibility($eid)
    {
        $themeApi = $this->getApi($eid);

        if(!is_null($themeApi)) {

            if(egame()->isDefault() && $themeApi->data->game[0] != "ALL")
                return false;

            if(!egame()->isDefault() && $themeApi->data->game[0] == "ALL")
                return true;

            if(!egame()->isDefault() && !in_array(egame()->getConfig('id'), $themeApi->data->game))
                return false;
        }

        return true;
    }

    /**
     * @param $eid
     * @return bool
     * @throws \Exception
     */
    public function remove($eid)
    {
        if(!$this->getThemeList()->has($eid))
            return false;

        $folderPath = extensions_path("Themes/{$this->__getFolderName($eid)}");

        if(!File::isWritable($folderPath))
            throw new \Exception($folderPath . " is not writable.");

        theme()->disable($eid);

        File::deleteDirectory($folderPath);

        return true;
    }

    /**
     * @param $link
     * @param $to
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function apiInstall($type, $link, $to, int $id = null)
    {
        $installer = $this->app->make(Installer::class, ["link" => $link]);

        $installer->download()->extractTo($to);

        if($id != null && $type != "private")
            tx_app()->api()->api->postDownload($type . ".theme", ["id" => $id]);

        $this->app["theme.cache"]->resetCache();

        return true;
    }

    public function updateConfiguration(Array $inputs)
    {
        if($this->isDefault())
            return false;

        if($this->getConfigurationJson())
            $config = collect($this->getConfigurationJson());
        else
            $config = collect([]);

        foreach($inputs as $key => $value) {
            $config->put($key, $value);
        }

        $this->app['cache']->forget($this->cacheConfigurationPrefix);

        return $this->app['files']->put(extensions_path("Themes/" . $this->getFolderName() . "/configuration.json"), $config->toJson(JSON_PRETTY_PRINT));
    }

    /**
     * @param $eid
     * @return mixed
     */
    public function getApi($eid, $type = "public")
    {
        $api = collect(tx_app()->api()->get($type . ".theme"));
        $data = collect([]);

        if($api->has('error'))
            return null;

        $api->each(function($v, $k) use(&$eid, &$data) {
            if($v->data->eid == $eid)
                $data->push($v);
        });

        return $data->get(0);
    }

    /**
     * @return bool
     */
    public function hasWaitingUpdate()
    {
        if($this->app['cache']->has($this->cacheWaitingUpdatePrefix))
            return $this->app['cache']->get($this->cacheWaitingUpdatePrefix);

        $data = collect([]);

        foreach($this->getThemeList() as $eid => $path) {
            if(!is_null($this->getApi($eid))) {
                $api = $this->getApi($eid);
                $config = $this->manager->getConfig($eid);

                if($api->update->version > $config['version'])
                    $data->push($eid);
            }
        }

        $this->app['cache']->put($this->cacheWaitingUpdatePrefix, $data->isNotEmpty(), now()->addMinutes(30));

        return $this->app['cache']->get($this->cacheWaitingUpdatePrefix);
    }

    /**
     * @return mixed
     */
    public function resetThemeLoaderCache()
    {
        return $this->app['cache']->forget($this->manager->cacheLoadThemes);
    }

    /**
     * @return mixed
     */
    public function resetThemeWaitingStatsCache()
    {
        return $this->app['cache']->forget($this->cacheWaitingUpdatePrefix);
    }

    /**
     * @param $slug
     */
    public function publishAssets($slug)
    {
        $directoryPublicAssets = public_path("extensions/themes/$slug");

        if(!$this->app['files']->exists($directoryPublicAssets))
            $this->app['files']->makeDirectory($directoryPublicAssets);

        $this->app['files']->copyDirectory(extensions_path("Themes/{$slug}/Assets"), $directoryPublicAssets, true);
    }

    /**
     * @return bool|void
     */
    public function publishAssetsForAll()
    {
        if($this->getThemeList()->isEmpty())
            return;

        foreach($this->getThemeList() as $eid => $path) {
            $this->publishAssets($this->__getFolderName($eid));
        }

        return true;
    }
}