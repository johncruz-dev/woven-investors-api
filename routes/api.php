<?php

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\InvestorController;
use App\Http\Controllers\Api\InvestorMetricsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('api.access')->group(function () {
    Route::post('/import', [ImportController::class, 'store'])
        ->middleware('throttle:api-import');

    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/metrics/average-age', [InvestorMetricsController::class, 'averageAge']);
        Route::get('/metrics/average-investment-amount', [InvestorMetricsController::class, 'averageInvestmentAmount']);
        Route::get('/metrics/total-investments', [InvestorMetricsController::class, 'totalInvestments']);

        Route::get('/investors', [InvestorController::class, 'index']);
    });
});
