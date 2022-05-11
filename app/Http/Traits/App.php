<?php

namespace App\Http\Traits;

trait App {

    /**
     * @parent App
     * @return App\Base
     */
    public function app()
    {
        return new \App\Http\Traits\App\Base();
    }

    /**
     * @parent Auth
     * @return Auth\Base
     * @throws \Exception
     */
    public function auth()
    {
        return new \App\Http\Traits\Auth\Base();
    }

    /**
     * @parent Model
     * @return Model\Base
     */
    public function model()
    {
        return new \App\Http\Traits\Model\Base();
    }
}