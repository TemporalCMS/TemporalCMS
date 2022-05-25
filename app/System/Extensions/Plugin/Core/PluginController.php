<?php

namespace App\System\Extensions\Plugin\Core;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;

abstract class PluginController extends Controller {

    public $admin = false;

    /**
     * @param $view
     * @param $title
     * @param array $data
     * @param string $access
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function view($view, $title, $data = [], $access = "")
    {
        $view = $this->getEid() . "::" . $view;

        if($this->admin)
            return parent::view($view, $title, $data, new AdminController());

        return parent::view($view, $title, $data);
    }

    /**
     * @return mixed
     */
    private function getEid()
    {
        return explode('__', explode('\\', get_class($this))[2])[1];
    }
}