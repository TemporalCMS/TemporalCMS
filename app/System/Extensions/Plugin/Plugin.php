<?php

namespace App\System\Extensions\Plugin;

use App\Http\Traits\App;
use App\System\Extensions\Plugin\Support\Assets;
use App\System\Extensions\Plugin\Support\Cache;
use App\System\Extensions\Plugin\Support\EEvent;
use App\System\Extensions\Plugin\Support\Module;
use App\System\Installer;
use App\System\Performer;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Plugin {

    use App;

    private $app;
    private $files;
    private $manager;

    private $pcache;
    private $cacheWaitingUpdatePrefix = "plugin_waiting_update_cache";
    private $cacheEnabledPlugins = "plugins_enabled_cache";

    private $pluginEnable = [];

    public function __construct(Application $app, Filesystem $files, Manager $manager)
    {
        $this->app = $app;
        $this->files = $files;
        $this->manager = $manager;
        $this->pcache = $this->app->make(Cache::class);

        $this->pluginEnable = collect([]);
    }

    /**
     * Load all plugins
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function start()
    {
        return $this->manager->load();
    }

    /**
     * @param $eidOrPath
     * @param $keys
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getConfig($eidOrPath, $keys = "")
    {
        $result = null;

        foreach($this->manager->getPluginsLoaded() as $eid => $path) {
            if ($eidOrPath == $eid || $eidOrPath == $path) {
                $result = $path;
            }
        }

        if($this->app()->api()->isDeveloper())
            return $keys == "" ? json_decode($this->files->get($result . "/config.json"), true) : Arr::dot(json_decode($this->files->get($result . "/config.json"), true))[$keys];

        return $keys == "" ? $this->pcache->configCache($result, json_decode($this->files->get($result . "/config.json"), true))[$result] : Arr::dot($this->pcache->configCache($result, json_decode($this->files->get($result . "/config.json"), true))[$result])[$keys];
    }

    /**
     * @param $eidOrPath
     * @param $keys
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __getConfig($eidOrPath, $keys = "")
    {
        $result = null;

        foreach($this->manager->getPluginsLoaded() as $eid => $path) {
            if ($eidOrPath == $eid || $eidOrPath == $path) {
                $result = $path;
            }
        }

        return $keys == "" ? json_decode($this->files->get($result . "/config.json"), true) : Arr::dot(json_decode($this->files->get($result . "/config.json"), true))[$keys];
    }

    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function getPluginsLoaded()
    {
        return $this->manager->getPluginsLoaded();
    }

    /**
     * @return array|\Illuminate\Support\Collection
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getPluginEnable()
    {
        if($this->app['cache']->has($this->cacheEnabledPlugins))
            return $this->app['cache']->get($this->cacheEnabledPlugins);

        if($this->getPluginsLoaded()->isEmpty() || $this->manager->getPluginJson() == [])
            return null;

        $plugins = collect([]);

        foreach($this->manager->getPluginJson() as $plugin) {
            foreach($this->manager->getPluginsList() as $val) {
                $eid = $this->getConfig($val, "id");

                if($plugin == $eid)
                    $plugins->put($eid, $val);
            }
        }

        $this->app['cache']->put($this->cacheEnabledPlugins, $plugins, now()->addMinutes(30));

        return $plugins;
    }

    /**
     * @param $aliase
     * @return bool
     * @throws \Exception
     */
    public function has($aliase)
    {
        if($this->getEidWithAliase($aliase)->isEmpty())
            return false;

        return $this->getPluginsLoaded()->has($this->getEidWithAliase($aliase)->get(0)['eid']);
    }

    /**
     * @param $eid
     * @return bool
     */
    public function exists($eid)
    {
        return $this->getPluginsLoaded()->has($eid);
    }

    /**
     * @param $EidOrAliase
     * @return mixed
     * @throws \Exception
     */
    public function if($EidOrAliase)
    {
        if($this->getEidWithAliase($EidOrAliase) && !$this->getEidWithAliase($EidOrAliase)->isEmpty())
            return $this->app->make(Module::class, ["eid" => $this->getEidWithAliase($EidOrAliase)->get(0)['eid']]);

        return $this->app->make(Module::class, ["eid" => $EidOrAliase]);
    }

    /**
     * @param $eid
     * @return bool|\Illuminate\Support\Collection|int
     */
    public function disable($eid)
    {
        if(!$this->getPluginsLoaded()->has($eid))
            return false;

        $this->app['plugin.navbar.admin']->resetCache();
        $this->app['plugin.navbar.user']->resetCache();
        $this->app['plugin.cache']->resetCache();

        $this->manager->updatePluginJson("remove", "$eid");

        return $this->resetPluginLoaderCache();
    }

    /**
     * @param $eid
     * @return bool|\Illuminate\Support\Collection|int
     * @throws FileNotFoundException
     */
    public function enable($eid)
    {
        if(!$this->getPluginsLoaded()->has($eid))
            return false;

        $this->app['plugin.navbar.admin']->resetCache();
        $this->app['plugin.navbar.user']->resetCache();
        $this->app['plugin.cache']->resetCache();

        if($this->checkCompatibility($eid)) {
            $this->manager->updatePluginJson("add", "$eid");
            $this->runMigrations($eid);

            $this->app->make(EEvent::class)->loadPhpFileEvent(extensions_path("Plugins/" . $this->getPluginFolderName($eid) . "/Config/Events/afterActivate.php"));
            $this->resetPluginLoaderCache();

            return $this->app->make(Performer::class)->resetCaches();
        }

        return false;
    }

    /**
     * @param $eid
     * @return bool|\Illuminate\Support\Collection|int
     */
    public function checkCompatibility($eid)
    {
        $pluginApi = $this->getApi($eid);

        if(!is_null($pluginApi)) {

            if(egame()->isDefault() && $pluginApi->data->game[0] != "ALL")
                return false;

            if(!egame()->isDefault() && $pluginApi->data->game[0] == "ALL")
                return true;

            if(!egame()->isDefault() && !in_array(egame()->getConfig('id'), $pluginApi->data->game))
                return false;
        }

        return true;
    }

    /**
     * @param string $aliase
     * @return bool|\Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getEidWithAliase(string $aliase)
    {
        if($this->getPluginsLoaded()->isEmpty())
            return false;

        $data = collect([]);

        $this->getPluginsLoaded()->each(function($path, $eid) use(&$aliase, &$data) {
            if($this->getConfig($eid, "aliases") == $aliase)
                $data->push(["eid" => $eid, "aliase" => $aliase, "path" => $path]);
        });

        if($data->count() > 1)
            throw new \Exception("Aliase {$aliase} already exist in another plugin.");

        return $data;
    }

    /**
     * @param string $eid
     * @return bool|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getAliaseWithEid(string $eid)
    {
        if($this->getPluginsLoaded()->isEmpty())
            return false;

        return $this->getConfig($eid, "aliases");
    }

    /**
     * @param string $eid
     * @return string
     * @throws FileNotFoundException
     */
    public function getPluginFolderName(string $eid, bool $full = false)
    {
        $name = $this->getConfig($eid, "slug");

        if($full)
            return extensions_path("Plugins/{$name}");

        return $name;
    }

    /**
     * @param $eid
     * @return bool
     * @throws FileNotFoundException
     */
    public function remove($eid)
    {
        if(!$this->getPluginsLoaded()->has($eid))
            return false;

        $folderPath = $this->getPluginFolderName($eid, true);

        if(!$this->files->isWritable($folderPath))
            throw new \Exception($folderPath . " is not writable.");

        if($this->if($eid)->isEnable())
            $this->disable($eid);

        $this->files->deleteDirectory($folderPath);

        $this->resetPluginLoaderCache();

        return true;
    }

    /**
     * @return mixed
     */
    public function el()
    {
        $eid = explode('__', explode('\\', request()->route()->getAction("namespace"))[2])[1];

        return $this->app->make(Assets::class, ['eid' => $eid]);
    }

    /**
     * @param bool $full
     * @return string
     * @throws FileNotFoundException
     */
    public function getCurrentPlugin($param = "", $full = false)
    {
        if(empty(explode('__', explode('\\', request()->route()->getAction("namespace"))[2])[1]))
            return null;

        $eid = explode('__', explode('\\', request()->route()->getAction("namespace"))[2])[1];

        if($param == "")
            return $this->getPluginFolderName($eid, $full);

        return $this->getConfig($this->getPluginFolderName($eid, true), $param);
    }

    /**
     * @param $eid
     * @return mixed
     */
    public function runMigrations($eid)
    {
        return $this->app['artisan']->call("plugin:migrate", ['module' => $eid]);
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
            tx_app()->api()->api->postDownload($type . ".plugin", ["id" => $id]);

        $this->app['plugin.navbar.admin']->resetCache();
        $this->app['plugin.navbar.user']->resetCache();
        $this->app['plugin.cache']->resetCache();

        return true;
    }

    /**
     * @param $eid
     * @return mixed
     */
    public function getApi($eid, $type = "public")
    {
        $api = collect(tx_app()->api()->get($type . ".plugin"));
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
     * @param $type
     * @param $link
     * @param $path
     * @param $eid
     * @throws FileNotFoundException
     */
    public function update($type, $link, $path, $eid)
    {
        $this->apiInstall($type, $link, $path);
        $this->runMigrations($eid);
        $this->resetPluginWaitingStatsCache();

        $this->app->make(EEvent::class)->loadPhpFileEvent(extensions_path("Plugins/" . $this->getPluginFolderName($eid) . "/Config/Events/afterUpdate.php"));
    }

    /**
     * @return bool
     * @throws FileNotFoundException
     */
    public function hasWaitingUpdate()
    {
        if($this->app['cache']->has($this->cacheWaitingUpdatePrefix))
            return $this->app['cache']->get($this->cacheWaitingUpdatePrefix);

        $data = collect([]);

        foreach($this->getPluginsLoaded() as $eid => $path) {
            if(!is_null($this->getApi($eid))) {
                $api = $this->getApi($eid);
                $config = $this->getConfig($eid);

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
    public function resetPluginLoaderCache()
    {
        return $this->app['cache']->forget($this->cacheEnabledPlugins);
    }

    /**
     * @return mixed
     */
    public function resetPluginWaitingStatsCache()
    {
        return $this->app['cache']->forget($this->cacheWaitingUpdatePrefix);
    }

    /**
     * @param $slug
     */
    public function publishAssets($slug)
    {
        $directoryPublicAssets = public_path("extensions/plugins/$slug");

        if(!$this->app['files']->exists($directoryPublicAssets))
            $this->app['files']->makeDirectory($directoryPublicAssets);

        $this->app['files']->copyDirectory(extensions_path("Plugins/{$slug}/Assets"), $directoryPublicAssets, true);
    }

    /**
     * @return bool|void
     * @throws FileNotFoundException
     */
    public function publishAssetsForAll()
    {
        if($this->getPluginEnable()->isEmpty())
            return;

        foreach($this->getPluginEnable() as $eid => $path) {
            $this->publishAssets($this->getPluginFolderName($eid));
        }

        return true;
    }

    /**
     * @param $eid
     * @return bool|\Illuminate\Support\Collection
     * @throws FileNotFoundException
     */
    public function getIncompatibility($eid)
    {
        $config = $this->getConfig($eid);
        $data = collect([]);

        if(!empty($config['incompatibility'])) {
            foreach($config['incompatibility'] as $id) {
                if($this->if($id)->isEnable()) {
                    $c = $this->getConfig($id);

                    $data->put($id, ["name" => $c['name'], "slug" => $c['slug']]);
                }
            }
        }

        if($data->isNotEmpty()) {
            Log::warning("Plugin {$config['slug']} was disabled because many plugins are not compatible with it.");

            $this->disable($eid);
        }


        return $data->isEmpty() ? false : $data;
    }

    /**
     * @param $eid
     * @return \Illuminate\Support\Collection
     * @throws FileNotFoundException
     */
    public function getDependencies($eid)
    {
        $config = $this->getConfig($eid);
        $data = [];

        if(!empty($config['dependencies'])) {

            if(!empty($config['dependencies']['Plugins'])) {
                foreach($config['dependencies']['Plugins'] as $id) {
                    if(!$this->if($id)->isEnable()) {
                        $c = $this->getConfig($id);

                        $data["Plugins"][$id] = ["name" => $c['name'], "slug" => $c['slug']];
                    }

                    if(empty($data["Plugins"])) {
                        $data["Plugins"] = [];
                    }
                }
            } else {
                $data["Plugins"] = [];
            }

            if(!empty($config['dependencies']['CMS'])) {
                $explode = explode(":", $config['dependencies']['CMS']);

                $symbol = $explode[0];
                $version = $explode[1];

                $cmsVers = $this->app()->update()->getVersion();

                if($symbol == "^") {
                    $data["CMS"] = $version > $cmsVers ? null : false;
                } elseif($symbol == "=") {
                    $data["CMS"] = $version == $cmsVers ? null : false;
                } elseif($symbol == "^=") {
                    $data["CMS"] = $version >= $cmsVers ? null : false;
                } elseif($symbol == "!") {
                    $data["CMS"] = $version != $cmsVers ? null : false;
                } elseif($symbol == "!^") {
                    $data["CMS"] = $version < $cmsVers ? null : false;
                } elseif($symbol == "!^=") {
                    $data["CMS"] = $version <= $cmsVers ? null : false;
                } else {
                    $data["CMS"] = null;
                }

            } else {
                $data["CMS"] = null;
            }
        } else {
            $data["Plugins"] = [];
            $data["CMS"] = null;
        }

        if(!empty($data["Plugins"])) {
            Log::warning("Plugin {$config['slug']} was disabled because it needs multiple plugins to work.");

            $this->disable($eid);
        }

        if(!is_null($data["CMS"]) && !$data["CMS"]) {
            Log::warning("Plugin {$config['slug']} is not supported with the current version of the cms.");

            $this->disable($eid);
        }

        return $data;
    }
}

