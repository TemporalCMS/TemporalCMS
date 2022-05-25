<?php

namespace App\System\App\Users;

use App\Http\Traits\App;

abstract class UsersAbstract {

    use App;

    protected $cache;
    protected $cachePrefix;

    public function __construct()
    {
        $this->cache = app('cache');
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function addToCaches($data)
    {
        return $this->cache->put($this->cachePrefix, $data, now()->addHour());
    }

    /**
     * @return |null
     */
    public function resetCache()
    {
        return $this->cache->has($this->cachePrefix) ? $this->cache->forget($this->cachePrefix) : null;
    }

}