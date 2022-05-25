<?php

namespace App\System\Extensions\Plugin;

use App\System\Performer;
use Composer\Autoload\ClassLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

class Manager {

    private $pluginJson;
    private $pluginsFolders;

    private $files;
    private $app;
    private $cache;

    private $cacheFlushPrefix = "plugins_cache_flush";
    private $cacheCompatibilityFlushPrefix = "plugins_cache_compatibility_flush";

    private $pluginsList = [];
    private $pluginsLoaded = [];

    /**
     * Manager constructor.
     * @param Filesystem $files
     * @param Application $app
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __construct(Filesystem $files, Application $app)
    {
        $this->files = $files;
        $this->app = $app;
        $this->cache = app('cache');

        $this->pluginJson = json_decode($this->files->get(storage_path("app/Extensions/plugin.json")));
        $this->pluginsFolders = $this->files->glob(extensions_path("Plugins/*"));

        $this->pluginsList = collect([]);
        $this->pluginsLoaded = collect([]);
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public function load()
    {
        if($this->pluginsFolders == [])
            return;

        $this->cacheFlusher();

        $this->loadPlugins();

        foreach($this->pluginsList as $path) {
            $config = $this->getConfigPlugins($path);

            $providers = $config->providers;
            $eid = $config->id;

            foreach($providers as $provider) {
                if(class_exists($provider))
                    $this->register($provider, $eid, $path);
            }

            $this->cacheCompatibilityFlusher($eid);
        }
    }

    /**
     * @param $eid
     * @return null
     */
    protected function cacheCompatibilityFlusher($eid)
    {
        if(!$this->cache->has($this->cacheCompatibilityFlushPrefix)) {
            if(plugin()->checkCompatibility($eid))
                return null;

            plugin()->disable($eid);

            $this->cache->put($this->cacheCompatibilityFlushPrefix, 1, now()->addHour());
        }
    }

    /**
     * @param $path
     * @return \Illuminate\Support\Collection|string
     */
    private function checkConfigPlugins($path)
    {
        $all_files_no_config = collect([]);

        foreach($path as $key) {
            if(!$this->files->exists($key . "/config.json"))
                $all_files_no_config->push($key);
        }

        if($all_files_no_config->isEmpty())
            return "none";

        return $all_files_no_config;
    }

    /**
     * @param $plugins
     * @param string $v
     * @return mixed|void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getConfigPlugins($plugins, $v = "")
    {
        if(!$this->files->exists($plugins . "/config.json"))
            return;

        return $v == "" ? json_decode($this->files->get($plugins . "/config.json")) : json_decode($this->files->get($plugins . "/config.json"))->{$v};
    }

    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function getPluginsLoaded()
    {
        return $this->pluginsLoaded;
    }

    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function getPluginsList()
    {
        return $this->pluginsList;
    }

    /**
     * @return mixed
     */
    public function getPluginJson()
    {
        return $this->pluginJson;
    }

    /**
     * @param $file
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function search($file)
    {
        return $this->files->get(base_path("Extensions/Plugins/{$file}"));
    }

    /**
     * @throws \Exception
     */
    private function cacheFlusher()
    {
        if($this->cache->has($this->cacheFlushPrefix))
            return;

        $checkerConfig = $this->checkConfigPlugins($this->pluginsFolders);

        $this->cache->put($this->cacheFlushPrefix, 1, now()->addHours(2));

        if($checkerConfig == "none")
            return;

        foreach($checkerConfig as $key) {
            $this->flushFolders($key);
        }
    }

    /**
     * @param $folders
     * @throws \Exception
     */
    private function flushFolders($folders)
    {
        if(!$this->files->isWritable($folders))
            throw new \Exception($folders . " isn't writable.");

        $this->files->deleteDirectory($folders);
    }

    /**
     * @return array|\Illuminate\Support\Collection|void
     * @throws \Exception
     */
    private function loadPlugins()
    {
        $folders = $this->listFolders();

        if($folders->isEmpty())
            return;

        foreach($folders as $file) {
            $config = $this->getConfigPlugins($file);

            if($this->verifyConfig($config))
                $this->pluginsList->push($file);
        }

        return $this->pluginsList;
    }

    /**
     * @return \Illuminate\Support\Collection|void
     */
    public function listFolders()
    {
        if($this->pluginsFolders == [])
            return;

        $i = collect([]);

        foreach($this->pluginsFolders as $folders) {
            $files = $this->files->files($folders);

            if($files != null && $files != []) {
                foreach($files as $file) {
                    if($file->getBasename() == "config.json")
                        $i->push($folders);
                }
            }
        }

        return $i;
    }

    /**
     * @param Object $config
     * @return bool
     * @throws \Exception
     */
    private function verifyConfig(Object $config)
    {
        if(!isset($config->id) && !isset($config->name) && !isset($config->author) && !isset($config->providers))
            throw new \Exception("Error, the config of one of the plugins is bad, please check it.");

        if(empty($config->providers) && empty($config->author) && empty($config->id) && empty($config->name))
            throw new \Exception("Error, the config of one of the plugins is empty, please check it.");

        if(!is_array($config->author) && !is_array($config->providers))
            throw new \Exception("Error, the config of one of the plugins has author or providers key who isn't an array, please check it.");

        return true;
    }

    /**
     * @param string $provider
     * @param string $eid
     * @param string $path
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function register(string $provider, string $eid, $path)
    {
        if(in_array($eid, (array) $this->getPluginJson())) {

            if($this->files->exists($path . "/vendor/autoload.php")) {
                $classLoader = $this->files->getRequire($path . "/vendor/autoload.php");
                $composer_json = json_decode($this->files->get($path . "/composer.json"), true);

                $this->autoloadComposer($classLoader, $composer_json, $path);
            }


            $this->app->register($provider);
        }

        return $this->pluginsLoaded->put($eid, $path);
    }

    /**
     * @param ClassLoader $classLoader
     * @param $composer
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function autoloadComposer(ClassLoader $classLoader, $composer, $pluginPath)
    {
        $autoload = $composer['autoload'] ?? [];
        $current_folder = $pluginPath;

        if(isset($autoload['psr-4'])) {
            foreach($autoload['psr-4'] as $namespace => $path) {
                $classLoader->addPsr4($namespace, $current_folder . "/" . $path);
            }
        }

        if(isset($autoload['files'])) {
            foreach ($autoload['files'] as $file) {
                $this->files->getRequire($current_folder . "/" . $file);
            }
        }
    }

    /**
     * @param $type
     * @param $eid
     * @return bool|\Illuminate\Support\Collection|int
     */
    public function updatePluginJson($type, $eid)
    {
        $plugin_json = collect($this->pluginJson);

        if($type == "remove" && in_array($eid, $plugin_json->toArray())) {
            foreach($plugin_json as $key => $value) {
                if($value == $eid)
                    $plugin_json->forget($key);
            }
        } else {
            if(in_array($eid, $plugin_json->toArray()))
                return $plugin_json;

            $plugin_json->push($eid);
        }

        return $this->files->put(storage_path("app/Extensions/plugin.json"), $plugin_json->toJson(JSON_PRETTY_PRINT));
    }
}