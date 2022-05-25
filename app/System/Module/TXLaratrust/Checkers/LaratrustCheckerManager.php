<?php

namespace App\System\Module\TXLaratrust\Checkers;

use Illuminate\Support\Facades\Config;
use App\System\Module\TXLaratrust\Checkers\Role\LaratrustRoleQueryChecker;
use App\System\Module\TXLaratrust\Checkers\User\LaratrustUserQueryChecker;
use App\System\Module\TXLaratrust\Checkers\Role\LaratrustRoleDefaultChecker;
use App\System\Module\TXLaratrust\Checkers\User\LaratrustUserDefaultChecker;

class LaratrustCheckerManager
{
    /**
     * The object in charge of checking the roles and permissions.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Return the right checker according to the configuration.
     *
     * @return \Laratrust\Checkers\LaratrustChecker
     */
    public function getUserChecker()
    {
        switch (Config::get('laratrust.checker', 'default')) {
            case 'default':
                return new LaratrustUserDefaultChecker($this->model);
            case 'query':
                return new LaratrustUserQueryChecker($this->model);
        }
    }

    /**
     * Return the right checker according to the configuration.
     *
     * @return \Laratrust\Checkers\LaratrustChecker
     */
    public function getRoleChecker()
    {
        switch (Config::get('laratrust.checker', 'default')) {
            case 'default':
                return new LaratrustRoleDefaultChecker($this->model);
            case 'query':
                return new LaratrustRoleQueryChecker($this->model);
        }
    }
}
