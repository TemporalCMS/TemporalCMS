<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    public function __construct()
    {
        
    }
    public function index()
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