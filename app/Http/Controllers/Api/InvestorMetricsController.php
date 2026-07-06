<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvestorMetricsService;
use Illuminate\Http\JsonResponse;

class InvestorMetricsController extends Controller
{
    public function __construct(
        private readonly InvestorMetricsService $investorMetricsService,
    ) {}

    public function averageAge(): JsonResponse
    {
        return response()->json($this->investorMetricsService->averageAge());
    }

    public function averageInvestmentAmount(): JsonResponse
    {
        return response()->json($this->investorMetricsService->averageInvestmentAmount());
    }

    public function totalInvestments(): JsonResponse
    {
        return response()->json($this->investorMetricsService->totalInvestments());
    }
}
