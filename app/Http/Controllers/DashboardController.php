<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the module dashboard (home screen with 4 zones).
     */
    public function __invoke(): View
    {
        return view('dashboard');
    }
}
