<?php

namespace App\System\App\Users;

use App\Http\Traits\App;

class Navbar extends UsersAbstract {

    protected $cachePrefix = "cache__system__app__users_navbar";

    /**
     * @return mixed
     */
    public function get()
    {
        if(!$this->cache->has($this->cachePrefix)) {
            $data = $this->model()->instancy('NavbarZ')->with('childs')->scopes('parent')->orderBy('position')->get();

            $this->addToCaches($data);
        }

        return $this->cache->get($this->cachePrefix);
    }
}