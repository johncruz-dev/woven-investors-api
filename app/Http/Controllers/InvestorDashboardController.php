<?php

namespace App\Http\Controllers;

class InvestorDashboardController extends Controller
{
    public function index()
    {
        return view('investors.dashboard');
    }
}
