<?php

namespace App\System\Extensions\EGame\Support;

use Illuminate\Foundation\Application;

class Assets {

    /**
     * @param $path
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function element($path)
    {
        $folder = egame()->getFolderName();

        $currentEGame = egame()->getCurrent()->first();
        $folder_name = !theme()->isDefault() ? theme()->getFolderName() : "";

        if(!theme()->isDefault() && $currentEGame != null) {
            $reformateViewName = "Themes.$folder_name.Extensions.EGames.$folder.$path";

            if(view()->exists($reformateViewName))
                return view($reformateViewName);
        }

        return view("EGames.{$folder}.resources.views.{$path}");
    }

    /**
     * @param $path
     * @return string
     */
    public function asset($path)
    {
        $folder = egame()->getFolderName();

        dd(route("assets.egame.public.file", ["file" => $path]));
        if(tx_app()->api()->isDeveloper())
            return route("assets.egame.public.file", ["file" => $path]);

        $file = str_replace(["::", ".."], ["/", ""], $path);

        return url("/extensions/egames/{$folder}/{$file}");
    }
}