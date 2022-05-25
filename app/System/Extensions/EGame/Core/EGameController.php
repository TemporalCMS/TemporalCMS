<?php

namespace App\System\Extensions\EGame\Core;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;

abstract class EGameController extends Controller {

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
        $view = "egame::" . $view;

        if($this->admin)
            return parent::view($view, $title, $data, new AdminController());

        return parent::view($view, $title, $data);
    }

    /**
     * @return mixed
     */
    public function getEid()
    {
        return egame()->getConfig('id');
    }

    /**
     * @return mixed
     */
    public function getFolderName()
    {
        return egame()->getFolderName();
    }
}