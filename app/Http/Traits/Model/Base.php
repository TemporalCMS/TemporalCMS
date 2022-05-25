<?php

namespace App\Http\Traits\Model;

use App\Models\Configuration;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class Base {

    public $user;
    public $config;
    public $role;
    public $permission;

    /**
     * Base constructor.
     */
    public function __construct()
    {
        if(config('app.install'))
            return false;

        $this->user = new User();
        $this->config = new Configuration();
        $this->role = new Role();
        $this->permission = new Permission();
    }

    /**
     * @param $model
     * @param string $namespace
     * @return mixed
     */
    public function instancy($model, $namespace = 'App\\Models\\')
    {
        $model = $namespace . $model;

        if(class_exists($model))
            return new $model;
        throw new ModelNotFoundException($model . " is not found");
    }

}