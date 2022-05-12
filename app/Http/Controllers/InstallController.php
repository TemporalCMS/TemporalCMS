<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstallController extends Controller
{


    public function home(Request $request)
    {
        $method = $request->method();


    }

    public function step1(Request $request)
    {
        $method = $request->method();

        if ($request->isMethod('post')) {
            
        }

    }

    public function step1_valid(Request $request)
    {

    }

    public function step2(Request $request)
    {

    }

    public function step2_set(Request $request)
    {

    }

    public function step3(Request $request)
    {

    }

    public function step4(Request $request)
    {

    }

}
