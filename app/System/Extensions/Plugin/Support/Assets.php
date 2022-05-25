<?php

namespace App\System\Extensions\Plugin\Support;

use Illuminate\Foundation\Application;

class Assets {

    private $eid;

    /**
     * Assets constructor.
     * @param $eid
     */
    public function __construct($eid)
    {
        $this->eid = $eid;
    }

    /**
     * @param $path
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function element($path)
    {
        $folder = plugin()->getPluginFolderName($this->eid);

        $currentPlugin = plugin()->getCurrentPlugin();
        $folder_theme = !theme()->isDefault() ? theme()->getFolderName() : "";

        if(!theme()->isDefault() && $currentPlugin != null) {
            $reformateViewName = "Themes.$folder_theme.Extensions.Plugins.$currentPlugin.$path";

            if(view()->exists($reformateViewName))
                return view($reformateViewName);
        }

        return view("Plugins.{$folder}.Resources.views.{$path}");
    }

    /**
     * @param $path
     * @return string
     */
    public function asset($path)
    {
        $folder = plugin()->getPluginFolderName($this->eid);

        if(tx_app()->api()->isDeveloper())
            return route("assets.plugin.public.file", ["file" => $path, "folder" => $folder]);

        $file = str_replace(["::", ".."], ["/", ""], $path);

        return url("/extensions/plugins/{$folder}/{$file}");
    }
}