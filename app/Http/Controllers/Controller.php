<?php

namespace App\Http\Controllers;

use App\Http\Traits\App;
use App\Http\Traits\Shortcuts;
use App\View\Composer\ThemeComposer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        if(config('app.install'))
            return false;
    }

    public function datatable_lang()
    {
        return json_encode($this->lang('datatable'));
    }

    public function view($view, $title, $data = [], $access = "")
    {
        if($data != [])
            View::share($data);

        return $this->app()->component()->view()->moduleSetViews(Route::getCurrentRoute()->getActionName(), $view, $title, $access == "" ? $this : $access);
    }

    public function storage_public_file($file)
    {
        $path = storage_path('app/public/' . $file);

        if (!File::exists($path)) {
            return abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    public function storage_public_avatar_file($file)
    {
        $path = storage_path('app/public/Avatar/' . $file);

        if (!File::exists($path)) {
            return abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        if(substr($type, 0, 5) != "image") {
            $response = Response::make(File::get(storage_path('app/public/Avatar/default_avatar.jpg')), 200);
            $response->header("Content-Type", "image/jpeg");

            return $response;
        }

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
}
