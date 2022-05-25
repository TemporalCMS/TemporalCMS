<?php

namespace App\System\App\Users\Admin;

use App\Http\Traits\App;
use App\System\App\Users\UsersAbstract;

class Actions extends UsersAbstract {

    protected $cachePrefix = "cache__system__app__users__admin_actions";

    /**
     * @return mixed
     */
    public function get()
    {
        if(!$this->cache->has($this->cachePrefix)) {
            $data = $this->model()->instancy('DashBoardActions')->orderByDesc('created_at')->get();

            $this->addToCaches($data);
        }

        return $this->cache->get($this->cachePrefix);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function add($name)
    {
        $this->resetCache();

        return collect($this->model()->instancy('DashBoardActions')->insert(['name' => $name, 'user_id' => $this->auth()->getId(), 'created_at' => now()]));
    }
}