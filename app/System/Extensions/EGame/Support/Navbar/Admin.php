<?php

namespace App\System\Extensions\EGame\Support\Navbar;

use App\Http\Traits\App;
use Illuminate\Foundation\Application;

class Admin {

    use App;

    private $app;

    private $navbarList = [];

    private $cachePrefix = "egame_navbar_admin_cache";

    /**
     * Admin constructor.
     */
    public function __construct(Application $application)
    {
        $this->app = $application;

        $this->navbarList = collect([]);
    }

    /**
     * @return void
     */
    public function getNavbarList()
    {
        if($this->app()->api()->isDeveloper())
            return $this->navbarList->all();

        if($this->app['cache']->has($this->cachePrefix))
            return $this->app['cache']->get($this->cachePrefix);

        $this->app['cache']->forever($this->cachePrefix, $this->navbarList->all());

        return $this->app['cache']->get($this->cachePrefix);
    }

    /**
     * @param array $data
     */
    public function addNavbar(Array $data = [])
    {
        $this->navbarList->push($data);
    }

    /**
     * @return bool
     */
    public function resetCache()
    {
        if($this->app['cache']->has($this->cachePrefix))
            return $this->app['cache']->forget($this->cachePrefix);

        return false;
    }
}