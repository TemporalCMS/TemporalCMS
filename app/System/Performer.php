<?php

namespace App\System;

use Illuminate\Foundation\Application;

class Performer {

    private $app;

    /**
     * Performer constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * Caches
     */
    public function caches()
    {
        if(tx_app()->api()->isDeveloper())
            return false;

        //if(!$this->app->routesAreCached())
            //$this->cacheRoutes();

        if(!$this->app->eventsAreCached())
            $this->cacheEvent();

        //$this->cacheViews();
    }

    /**
     * @return mixed
     */
    protected function cacheViews()
    {
        return $this->app['artisan']->call('view:cache');
    }

    /**
     * @return mixed
     */
    protected function cacheRoutes()
    {
        return $this->app['artisan']->call("route:cache");
    }

    /**
     * @return mixed
     */
    protected function cacheEvent()
    {
        return $this->app['artisan']->call("event:cache");
    }

    /**
     * @return mixed
     */
    protected function flushCacheViews()
    {
        return $this->app['artisan']->call('view:clear');
    }

    /**
     * @return mixed
     */
    protected function flushCacheRoutes()
    {
        return $this->app['artisan']->call("route:clear");
    }

    /**
     * @return mixed
     */
    protected function flushCacheEvent()
    {
        return $this->app['artisan']->call("event:clear");
    }

    /**
     * Flush all caches
     */
    public function reset()
    {
        //$this->flushCacheViews();
        $this->flushCacheEvent();
        $this->flushCacheRoutes();
    }

    /**
     * Reset caches
     */
    public function resetCaches()
    {
        $this->reset();
        $this->caches();
    }

}