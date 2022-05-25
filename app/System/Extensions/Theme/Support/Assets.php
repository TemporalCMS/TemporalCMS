<?php

namespace App\System\Extensions\Theme\Support;

use Illuminate\Foundation\Application;

class Assets {

    /**
     * @param $path
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function element($path)
    {
        $folder = theme()->getFolderName();

        return view("Themes.{$folder}.Includes.{$path}");
    }

    /**
     * @param $path
     * @return string
     */
    public function asset($path)
    {
        if(tx_app()->api()->isDeveloper())
            return route("assets.theme.public.file", ["file" => $path]);

        $file = str_replace(["::", ".."], ["/", ""], $path);
        $folder = theme()->getFolderName();

        return url("/extensions/themes/{$folder}/{$file}");
    }
}