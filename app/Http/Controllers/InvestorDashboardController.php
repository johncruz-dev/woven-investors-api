<?php

namespace App\Http\Controllers;

class InvestorDashboardController extends Controller
{
    public function index()
    {
        return view('investors.dashboard', [
            'apiBearerToken' => config('security.dashboard.bearer_token'),
            'apiKey' => config('security.dashboard.api_key'),
        ]);
    }
}
