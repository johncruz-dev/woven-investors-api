<?php

namespace Tests\Unit\Services;

use App\Models\Investment;
use App\Models\Investor;
use App\Repositories\InvestmentRepository;
use App\Repositories\InvestorRepository;
use App\Services\InvestorMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvestorMetricsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InvestorMetricsService(
            new InvestorRepository,
            new InvestmentRepository,
        );
    }

    public function test_it_calculates_average_age(): void
    {
        Investor::factory()->create(['age' => 30]);
        Investor::factory()->create(['age' => 50]);

        $result = $this->service->averageAge();

        $this->assertSame(40.0, $result['average_age']);
    }

    public function test_it_calculates_average_investment_amount(): void
    {
        $investor = Investor::factory()->create();
        Investment::factory()->create(['investor_id' => $investor->id, 'amount' => 100, 'investment_date' => '2024-01-01']);
        Investment::factory()->create(['investor_id' => $investor->id, 'amount' => 300, 'investment_date' => '2024-02-01']);

        $result = $this->service->averageInvestmentAmount();

        $this->assertSame(200.0, $result['average_investment_amount']);
    }

    public function test_it_counts_total_investments(): void
    {
        $investor = Investor::factory()->create();
        Investment::factory()->create(['investor_id' => $investor->id, 'investment_date' => '2024-01-01']);
        Investment::factory()->create(['investor_id' => $investor->id, 'investment_date' => '2024-02-01']);

        $result = $this->service->totalInvestments();

        $this->assertSame(2, $result['total_investments']);
    }
}
