<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    
    public function index()
    {
        $apps = config('portal.apps');

        return view('dashboard.index', compact('apps'));
    }
}