<?php

namespace App\Http\Middleware;

use App\Http\Traits\App;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;

class CheckForMaintenanceMode extends Middleware
{
    use App;
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */

    protected $haveAccess;

    protected $except;

    public function __construct(Application $app)
    {
        if(config('app.install')) {
            if($app->isDownForMaintenance())
                Artisan::call("up");

            return parent::__construct($app);
        }

        $this->haveAccess = ($this->auth()->isLogin() && $this->model()->user->getUser()->hasRole('admin') || $this->auth()->isLogin() && $this->model()->user->getUser()->hasPermission('ACCESS__MAINTENANCE')) ? true : false;

        $get_authorized_uri = json_decode(site_config('maintenance_uri'), true);

        array_push($get_authorized_uri, "/users/login", "/assets/*", "/storage/*");
        array_filter($get_authorized_uri);

        $this->except = ($this->haveAccess) ? ["*"] : bypass()->getRoutesItem()->merge($get_authorized_uri)->toArray();

        View::share(['maintenance_desc' => (site_config('maintenance_desc') != null) ? base64_decode(site_config('maintenance_desc')) : null]);

        parent::__construct($app);
    }
}