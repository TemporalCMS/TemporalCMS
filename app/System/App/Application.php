<?php

namespace App\System\App;

use App\System\App\Users\Admin\Actions;
use App\System\App\Users\Navbar;
use App\System\App\Users\News;
use App\System\App\Users\Slider;

class Application {

    protected $fluencer = [];

    /**
     * Application constructor.
     */
    public function __construct()
    {
        $this->fluencer = collect([
            "users.navbar" => Navbar::class,
            "users.news" => News::class,
            "users.admin.actions" => Actions::class,
            "users.slider" => Slider::class
        ]);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->fluencer->all();
    }

    /**
     * @param $name
     * @param $class
     * @throws \Exception
     */
    public function register($name, $class)
    {
        if(!class_exists($class))
            throw new \Exception($class . " class don't exists");

        $this->fluencer->put($name, $class);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function use(string $key)
    {
        return $this->fluencer->has($key) ? app($this->fluencer->get($key)) : null;
    }
}