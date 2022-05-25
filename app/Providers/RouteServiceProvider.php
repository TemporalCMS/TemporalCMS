<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    
    
     /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';


    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

     /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapMainRoutes();
        $this->mapAdminRoutes();
        $this->mapInstallRoutes();
        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapMainRoutes()
    {
        Route::middleware(['web', 'ICheck'])->namespace($this->namespace)->group(base_path('routes/web.php'));
    }

    protected function mapInstallRoutes()
    {
        Route::middleware(['web'])->namespace($this->namespace)->group(base_path('routes/install.php'));
    }

    protected function mapAdminRoutes()
    {
        Route::middleware(['web', 'ICheck'])
            ->namespace($this->namespace)
            ->group(base_path('routes/admin.php'));
    }

    protected function mapApiRoutes()
    {
        Route::middleware(['api', 'ICheck'])
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
