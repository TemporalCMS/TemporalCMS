<?php

namespace App\System\Extensions\Theme;

use App\Http\Traits\App;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class Manager {

    use App;

    private $themeJson;

    private $files;
    private $app;
    private $cache;

    private $cacheCompatibilityFlushPrefix = "themes_cache_compatibility_flush";
    public $cacheLoadThemes = "themes_cache_load_themes";

    private $themeList = [];
    private $themeFolders = [];

    private $currentTheme = [];

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
        $this->cache = $this->app["theme.cache"];

        $this->themeJson = json_decode($this->files->get(storage_path("app/Extensions/theme.json")));
        $this->themeList = collect([]);
        $this->themeFolders = $this->files->glob(extensions_path("Themes/*"));
        $this->currentTheme = collect([]);
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function load()
    {
        if($this->themeFolders == [])
            return;

        $this->loadThemes();
        $this->loadCurrent();

        if(!theme()->isDefault()) {
            $this->cacheCompatibilityFlusher(theme()->get('id'));
        }
    }

    private function loadCurrent()
    {
        if($this->themeJson == [] || empty($this->themeJson) || !current($this->themeJson))
            return $this->currentTheme->put("default", "default");

        $eid = collect(($this->themeJson))->first();

        return $this->currentTheme->put($eid, $this->getConfig($eid));
    }

    /**
     * @param $eid
     * @return null
     */
    protected function cacheCompatibilityFlusher($eid)
    {
        $cache = app('cache');

        if(!$cache->has($this->cacheCompatibilityFlushPrefix)) {
            if(theme()->checkCompatibility($eid))
                return null;

            theme()->disable();

            $cache->put($this->cacheCompatibilityFlushPrefix, 1, now()->addHour());
        }
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

        foreach($this->getThemeList() as $eid => $path) {
            if ($eidOrPath == $eid || $eidOrPath == $path) {
                $result = $path;
            }
        }

        if($this->app()->api()->isDeveloper()) {
            if($keys == "")
                return json_decode($this->files->get($result . "/config.json"), true);

            return Arr::dot(json_decode($this->files->get($result . "/config.json"), true))[$keys];
        }

        if($keys == "")
            return $this->cache->configCache($result, json_decode($this->files->get($result . "/config.json"), true))[$result];

        return Arr::dot($this->cache->configCache($result, json_decode($this->files->get($result . "/config.json"), true))[$result])[$keys];
    }

    /**
     * @return array|\Illuminate\Support\Collection|void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function loadThemes()
    {
        if($this->app()->api()->isDeveloper()) {
            foreach($this->themeLoader() as $key => $value) {
                $this->themeList->put($key, $value);
            }

            return $this->themeList;
        }

        if(Cache::has($this->cacheLoadThemes)) {
            foreach(Cache::get($this->cacheLoadThemes) as $key => $value) {
                $this->themeList->put($key, $value);
            }

            return $this->themeList;
        }

        Cache::forever($this->cacheLoadThemes, $this->themeLoader());

        foreach(Cache::get($this->cacheLoadThemes) as $key => $value) {
            $this->themeList->put($key, $value);
        }

        return $this->themeList;
    }

    private function themeLoader()
    {
        $folders = $this->listFolders();
        $themeList = collect([]);

        if($folders->isEmpty())
            return;

        foreach($folders as $file) {
            $config = $this->getConfigThemes($file);

            if($this->verifyConfig($config))
                $themeList->put($config['id'], $file);
        }

        return $themeList;
    }

    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function getThemeList()
    {
        return $this->themeList;
    }

    /**
     * @param $themes
     * @param string $v
     * @return mixed|void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getConfigThemes($themes, $v = "")
    {
        if(!$this->files->exists($themes . "/config.json"))
            return;

        return $v == "" ? json_decode($this->files->get($themes . "/config.json"), true) : json_decode($this->files->get($themes . "/config.json"))->{$v};
    }

    /**
     * @return \Illuminate\Support\Collection|void
     */
    public function listFolders()
    {
        if($this->themeFolders == [])
            return;

        $i = collect([]);

        foreach($this->themeFolders as $folders) {
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
    private function verifyConfig($config)
    {
        if(!isset($config['id']) && !isset($config['name']) && !isset($config['author']))
            throw new \Exception("Error, the config of one of the theme is bad, please check it.");

        if(empty($config['author']) && empty($config['id']) && empty($config['name']))
            throw new \Exception("Error, the config of one of the theme is empty, please check it.");

        if(!is_array($config['author']))
            throw new \Exception("Error, the config of one of the theme has author key who isn't an array, please check it.");

        return true;
    }

    /**
     * @param $type
     * @param $eid
     * @return bool|\Illuminate\Support\Collection|int
     */
    public function setTheme($type, $eid)
    {
        $theme_json = collect($this->themeJson);

        if($type == "remove") {
            if(!in_array($eid, $theme_json->toArray()))
                return false;

            foreach($theme_json as $key => $value) {
                $theme_json->forget($key);
            }
        } else {
            if(in_array($eid, $theme_json->toArray()))
                return false;

            foreach($theme_json as $key => $value) {
                $theme_json->forget($key);
            }

            $theme_json->push($eid);
        }

        return $this->files->put(storage_path("app/Extensions/theme.json"), $theme_json->toJson(JSON_PRETTY_PRINT));
    }

    /**
     * @return array|\Illuminate\Support\Collection
     */
    public function getCurrentTheme()
    {
        return $this->currentTheme;
    }

}