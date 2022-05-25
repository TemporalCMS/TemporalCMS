<?php

namespace App\System\App\Users;

use App\Http\Traits\App;

class News extends UsersAbstract {

    protected $cachePrefix = "cache__system__app__users_news";

    /**
     * @return mixed
     */
    public function get()
    {
        if(!$this->cache->has($this->cachePrefix)) {
            $data = $this->model()->instancy("NewsList")->get();

            $this->addToCaches($data);
        }

        return $this->cache->get($this->cachePrefix);
    }
}