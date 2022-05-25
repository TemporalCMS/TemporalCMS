<?php

namespace App\System\Extensions\EGame\Support;

use Illuminate\Foundation\Application;

class Cache {

    private $app;

    private $cachePrefix = [
        "egames_cache_config"
    ];

    /**
     * Cache constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * @param $path
     * @param $config
     * @return mixed
     */
    public function configCache($path, $config)
    {
        $data = collect([]);

        if($this->app['cache']->has($this->cachePrefix[0])) {
            $cache_config = collect(json_decode($this->app['cache']->get($this->cachePrefix[0]), true));

            if(!$cache_config->has($path)) {
                $cache_config->put($path, $config);

                $this->app['cache']->forever($this->cachePrefix[0], $cache_config->toJson());
            }

        } else {
            $data->put($path, $config);

            $this->app['cache']->forever($this->cachePrefix[0], $data->toJson());
        }

        return json_decode($this->app['cache']->get($this->cachePrefix[0]), true);
    }

    /**
     * @return bool
     */
    public function hasConfigCache()
    {
        return $this->app['cache']->has($this->cachePrefix[0]) ? true : false;
    }

    /**
     * @return bool
     */
    public function resetCache()
    {
        if($this->app['cache']->has($this->cachePrefix[0]))
            return $this->app['cache']->forget($this->cachePrefix[0]);

        return false;
    }
}