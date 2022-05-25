<?php

namespace App\System\Extensions\EGame;

use App\Events\Extensions\EGames\afterActivate;
use App\Http\Traits\App;
use App\System\Extensions\EGame\Support\Assets;
use App\System\Extensions\EGame\Support\Cache;
use App\System\Extensions\EGame\Support\EEvent;
use App\System\Extensions\EGame\Support\Module;
use App\System\Installer;
use App\System\Performer;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class EGame {

    use App;

    private $app;
    public $manager;

    private $ecache;

    private $cacheDefaultPrefix = "egame_cache_default";
    private $cacheWaitingUpdatePrefix = "egame_waiting_update_cache";

    /**
     * EGame constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
        $this->manager = $this->app->make(Manager::class);
        $this->ecache = $this->app->make(Cache::class);
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
        $this->app['egame.cache']->resetCache();
        $this->app['egame.navbar.admin']->resetCache();

        $this->manager->setEGame("add", "$eid");
        $this->runMigrations($eid);

        $this->app->make(EEvent::class)->loadPhpFileEvent(extensions_path("EGames/" . $this->__getFolderName($eid) . "/src/Config/Events/afterActivate.php"));
    }

    /**
     * @return mixed
     */
    public function disable($eid = "")
    {
        $this->app['egame.cache']->resetCache();
        $this->app['egame.navbar.admin']->resetCache();

        if($this->isDefault())
            return false;

        if($eid == "")
            return $this->manager->setEGame("remove", $this->getCurrent()->first()['id']);

        return $this->manager->setEGame("remove", $eid);
    }

    /**
     * @return mixed
     */
    public function getCurrent()
    {
        return $this->manager->getCurrentEGame();
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
    public function getConfig($keys, $arr = false)
    {
        if(empty($this->getCurrent()->first()))
            return null;

        if($arr)
            return $this->getCurrent()->first()[$keys];

        return Arr::dot($this->getCurrent()->first())[$keys];
    }

    /**
     * @param $eid
     * @return mixed
     * @throws \Exception
     */
    public function __getConfig($eid)
    {
        if(!$this->getEGamesLoaded()->has($eid))
            throw new \Exception($eid . " don't exist.");

        return $this->manager->getConfig($eid);
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
    public function getEGameList()
    {
        return $this->manager->getEGameList();
    }

    /**
     * @return mixed
     */
    public function getEGamesLoaded()
    {
        return $this->manager->getEgamesLoaded();
    }

    /**
     * @param $eid
     * @return bool
     * @throws \Exception
     */
    public function remove($eid)
    {
        if(!$this->getEGameList()->has($eid))
            return false;

        $folderPath = extensions_path("EGames/{$this->__getFolderName($eid)}");

        if(!File::isWritable($folderPath))
            throw new \Exception($folderPath . " is not writable.");

        egame()->disable($eid);

        File::deleteDirectory($folderPath);

        return true;
    }

    /**
     * @param $eid
     * @return mixed
     */
    public function runMigrations($eid)
    {
        return $this->app['artisan']->call("egame:migrate", ["eid" => $eid]);
    }

    /**
     * @return mixed
     */
    public function app()
    {
        return $this->app->make("Extensions\\EGames\\{$this->getFolderName()}\\src\\Application\\Game");
    }

    /**
     * @param $link
     * @param $to
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function apiInstall($link, $to, int $id = null)
    {
        $installer = $this->app->make(Installer::class, ["link" => $link]);

        $installer->download()->extractTo($to);

        if($id != null)
            tx_app()->api()->api->postDownload("egame", ["id" => $id]);

        $this->app['egame.cache']->resetCache();
        $this->app['egame.navbar.admin']->resetCache();

        return true;
    }

    /**
     * @param $eid
     * @return mixed
     */
    public function getApi($eid)
    {
        $api = collect(tx_app()->api()->get('egame'));
        $data = collect([]);

        $api->each(function($v, $k) use(&$eid, &$data) {
            if($v->data->eid == $eid)
                $data->push($v);
        });

        return $data->get(0);
    }

    /**
     * @param $link
     * @param $slug
     * @param $eid
     * @throws \Exception
     */
    public function update($link, $slug, $eid)
    {
        $this->apiInstall($link, $slug);
        $this->runMigrations($eid);
        $this->resetEGameWaitingStatsCache();

        $this->app->make(EEvent::class)->loadPhpFileEvent(extensions_path("EGames/" . $this->__getFolderName($eid) . "/src/Config/Events/afterUpdate.php"));
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasWaitingUpdate()
    {
        if($this->app['cache']->has($this->cacheWaitingUpdatePrefix))
            return $this->app['cache']->get($this->cacheWaitingUpdatePrefix);

        $data = collect([]);

        foreach($this->getEGamesLoaded() as $eid => $path) {
            if(!is_null($this->getApi($eid))) {
                $api = $this->getApi($eid);
                $config = $this->__getConfig($eid);

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
    public function resetEGameWaitingStatsCache()
    {
        return $this->app['cache']->forget($this->cacheWaitingUpdatePrefix);
    }

    /**
     * @param $slug
     */
    public function publishAssets($slug)
    {
        $directoryPublicAssets = public_path("extensions/egames/$slug");

        if(!$this->app['files']->exists($directoryPublicAssets))
            $this->app['files']->makeDirectory($directoryPublicAssets);

        $this->app['files']->copyDirectory(extensions_path("EGames/{$slug}/assets"), $directoryPublicAssets, true);
    }

    /**
     * @return bool
     */
    public function publishAssetsForCurrent()
    {
        $slug = $this->getFolderName();

        $this->publishAssets($slug);

        return true;
    }
}