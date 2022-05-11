<?php

namespace App\Http\Middleware;

use Closure;

class ICheck
{
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
            return redirect()->to('/install');
        return $next($request);
    }
}
