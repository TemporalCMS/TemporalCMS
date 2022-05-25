<?php

namespace App\System\App\Users;

use App\Http\Traits\App;
use App\TModels\Sliders;

class Slider extends UsersAbstract {

    protected $cachePrefix = "cache__system__app__users_slider";

    /**
     * @return mixed
     */
    public function get()
    {
        if(!$this->cache->has($this->cachePrefix)) {
            $data = Sliders::orderBy('priority')->get();

            $this->addToCaches($data);
        }

        return $this->cache->get($this->cachePrefix);
    }
}