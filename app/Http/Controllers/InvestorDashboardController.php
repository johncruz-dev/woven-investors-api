<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvestorDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view('investors.dashboard', [
            'user' => $user,
            'canImport' => $user ? $user->canImport() : ! config('security.auth_required'),
            'authRequired' => (bool) config('security.auth_required'),
            'apiBearerToken' => config('security.dashboard.bearer_token'),
            'apiKey' => config('security.dashboard.api_key'),
        ]);
    }
}
