<?php

namespace App\System;

use Illuminate\Foundation\Application;

class Bypass {

    private $app;

    private $routes;

    /**
     * Performer constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
        $this->routes = collect([]);
    }

    /**
     * @param $route
     * @return \Illuminate\Support\Collection
     */
    public function maintenance($route)
    {
        return $this->routes->push($route);
    }

    public function getRoutesItem()
    {
        return $this->routes;
    }
}