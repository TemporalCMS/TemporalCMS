<?php

namespace App\System\Extensions\EGame;

use App\Http\Traits\App;
use App\System\Performer;
use Composer\Autoload\ClassLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;

class Manager {

    use App;

    private $egameJson;

    private $files;
    private $app;
    private $cache;

    private $egameList = [];
    private $egameFolders = [];
    private $egamesLoaded = [];

    private $currentEgame = [];

    /**
     * Manager constructor.
     * @param Application $application
     * @param Filesystem $files
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __construct(Application $application, Filesystem $files)
    {
        $this->app = $application;
        $this->files = $files;
        $this->cache = $this->app["egame.cache"];

        $this->egameJson = collect(json_decode($this->files->get(storage_path("app/Extensions/egame.json"))));
        $this->egameList = collect([]);
        $this->egameFolders = $this->files->glob(extensions_path("EGames/*"));
        $this->currentEgame = collect([]);
        $this->egamesLoaded = collect([]);
    }

    /**
     * @throws \Exception
     */
    public function load()
    {
        if($this->egameFolders == [])
            return;

        $this->loadEGames();
        $this->loadCurrent();

        foreach($this->egameList as $path) {
            $config = $this->getConfigEGames($path);

            $core = $config['core'];
            $eid = $config['id'];

            if(class_exists($core))
                $this->register($core, $eid, $path);
        }
    }

    private function loadCurrent()
    {
        if($this->egameJson == [] || empty($this->egameJson) || !current($this->egameJson))
            return $this->egameJson->put("default", "default");

        $eid = collect(($this->egameJson))->first();

        return $this->currentEgame->put($eid, $this->getConfig($eid));
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

        foreach($this->getEGameList() as $eid => $path) {
            if ($eidOrPath == $eid || $eidOrPath == $path) {
                $result = $path;
            }
        }

        if($this->app()->api()->isDeveloper()) {
            if($keys == "")
                return include($result . "/game.php");

            return Arr::dot(include($result . "/game.php"))[$keys];
        }

        if($keys == "")
            return $this->cache->configCache($result, include($result . "/game.php"))[$result];

        return Arr::dot($this->cache->configCache($result, include($result . "/game.php"))[$result])[$keys];
    }

    /**
     * @return array|\Illuminate\Support\Collection|void
     * @throws \Exception
     */
    private function loadEGames()
    {
        $folders = $this->listFolders();

        if($folders->isEmpty())
            return;

        foreach($folders as $file) {
            $config = $this->getConfigEGames($file);

            if($this->verifyConfig($config))
                $this->egameList->put($config['id'], $file);
        }

        return $this->egameList;
    }

    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function getEGameList()
    {
        return $this->egameList;
    }

    /**
     * @param $egames
     * @param string $v
     * @return mixed|void
     */
    public function getConfigEGames($egames, $v = "")
    {
        if(!$this->files->exists($egames . "/game.php"))
            return;

        return $v == "" ? include $egames . "/game.php" : include($egames . "/game.php")[$v];
    }

    /**
     * @return \Illuminate\Support\Collection|void
     */
    public function listFolders()
    {
        if($this->egameFolders == [])
            return;

        $i = collect([]);

        foreach($this->egameFolders as $folders) {
            $files = $this->files->files($folders);

            if($files != null && $files != []) {
                foreach($files as $file) {
                    if($file->getBasename() == "game.php")
                        $i->push($folders);
                }
            }
        }

        return $i;
    }

    /**
     * @param array $config
     * @return bool
     * @throws \Exception
     */
    private function verifyConfig(Array $config)
    {
        if(!isset($config['id']) && !isset($config['author']) && !isset($config['version']) && !isset($config['core']))
            throw new \Exception("Error, the config of one of the egame is bad, please check it.");

        if(empty($config['core']) && empty($config['author']) && empty($config['id']) && empty($config['version']))
            throw new \Exception("Error, the config of one of the egame is empty, please check it.");

        if(!is_array($config['author']))
            throw new \Exception("Error, the config of one of the egame has author or providers key who isn't an array, please check it.");

        return true;
    }

    /**
     * @param $type
     * @param $eid
     * @return bool|\Illuminate\Support\Collection|int
     */
    public function setEGame($type, $eid)
    {
        $egame_json = collect($this->egameJson);

        if($type == "remove") {
            if(!in_array($eid, $egame_json->toArray()))
                return false;

            foreach($egame_json as $key => $value) {
                $egame_json->forget($key);
            }
        } else {
            if(in_array($eid, $egame_json->toArray()))
                return false;

            foreach($egame_json as $key => $value) {
                $egame_json->forget($key);
            }

            $egame_json->push($eid);
        }

        $this->files->put(storage_path("app/Extensions/egame.json"), $egame_json->toJson(JSON_PRETTY_PRINT));

        return $this->app->make(Performer::class)->resetCaches();
    }

    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function getCurrentEGame()
    {
        return $this->currentEgame;
    }

    /**
     * @param string $core
     * @param string $eid
     * @param string $path
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function register(string $core, string $eid, $path)
    {
        if(in_array($eid, $this->getEgameJson()->toArray())) {
            if($this->files->exists($path . "/vendor/autoload.php")) {
                $classLoader = $this->files->getRequire($path . "/vendor/autoload.php");
                $composer_json = json_decode($this->files->get($path . "/composer.json"), true);

                $this->autoloadComposer($classLoader, $composer_json);
            }

            $this->app->register($core);
        }

        return $this->egamesLoaded->put($eid, $path);
    }

    /**
     * @param ClassLoader $classLoader
     * @param $composer
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function autoloadComposer(ClassLoader $classLoader, $composer)
    {
        $autoload = $composer['autoload'] ?? [];
        $current_folder = egame()->getFolderName();

        if(isset($autoload['psr-4'])) {
            foreach ($autoload['psr-4'] as $namespace => $path) {
                $classLoader->addPsr4($namespace, extensions_path("EGames/" . $current_folder . "/" . $path));
            }
        }

        if(isset($autoload['files'])) {
            foreach ($autoload['files'] as $file) {
                $this->files->getRequire(extensions_path("EGames/" . $current_folder . "/" . $file));
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getEgameJson(): \Illuminate\Support\Collection
    {
        return $this->egameJson;
    }

    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function getEgamesLoaded()
    {
        return $this->egamesLoaded;
    }
}