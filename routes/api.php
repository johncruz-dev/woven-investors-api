<?php

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\InvestorController;
use App\Http\Controllers\Api\InvestorMetricsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/import', [ImportController::class, 'store']);

    Route::get('/metrics/average-age', [InvestorMetricsController::class, 'averageAge']);
    Route::get('/metrics/average-investment-amount', [InvestorMetricsController::class, 'averageInvestmentAmount']);
    Route::get('/metrics/total-investments', [InvestorMetricsController::class, 'totalInvestments']);

    Route::get('/investors', [InvestorController::class, 'index']);
});
