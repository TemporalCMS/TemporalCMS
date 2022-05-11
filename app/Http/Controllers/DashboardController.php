<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        {
            

            if ($request->user()->hasRole('user')) {
                return redirect('/');
            }
    
            if ($request->user()->hasRole('admin')) {
                return view('application');
            }
            
        }
    }
}
