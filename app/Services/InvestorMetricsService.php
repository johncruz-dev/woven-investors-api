<?php

namespace App\Services;

use App\Repositories\InvestmentRepository;
use App\Repositories\InvestorRepository;

class InvestorMetricsService
{
    public function __construct(
        private readonly InvestorRepository $investorRepository,
        private readonly InvestmentRepository $investmentRepository,
    ) {}

    public function averageAge(): array
    {
        return [
            'average_age' => round((float) $this->investorRepository->getAverageAge(), 2),
        ];
    }

    public function averageInvestmentAmount(): array
    {
        return [
            'average_investment_amount' => round((float) $this->investmentRepository->getAverageAmount(), 2),
        ];
    }

    public function totalInvestments(): array
    {
        return [
            'total_investments' => $this->investmentRepository->getTotalCount(),
        ];
    }
}
