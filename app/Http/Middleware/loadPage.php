<?php

namespace App\Http\Middleware;

use App\Events\onLoadPage;
use App\Http\Traits\App;
use App\Http\Traits\Modules;
use Closure;
use Illuminate\Support\Facades\Route;

class loadPage
{
    use App;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(config('app.install'))
            return $next($request);

        event(new onLoadPage($request->ip()));

        return $next($request);
    }
}
