<?php

namespace App\System\Module\TXLaratrust\Models;

/**
 * This file is part of Laratrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Laratrust
 */
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use App\System\Module\TXLaratrust\Traits\LaratrustRoleTrait;
use App\System\Module\TXLaratrust\Contracts\LaratrustRoleInterface;

class LaratrustRole extends Model implements LaratrustRoleInterface
{
    use LaratrustRoleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('laratrust.tables.roles');
    }
}
