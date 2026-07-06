<?php

use App\Http\Controllers\InvestorDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InvestorDashboardController::class, 'index']);
