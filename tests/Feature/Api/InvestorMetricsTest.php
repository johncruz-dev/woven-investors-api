<?php

namespace Tests\Feature\Api;

use App\Models\Investment;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_average_age_endpoint(): void
    {
        Investor::factory()->create(['age' => 40]);
        Investor::factory()->create(['age' => 60]);

        $this->getJson('/api/v1/metrics/average-age')
            ->assertOk()
            ->assertJson(['average_age' => 50]);
    }

    public function test_average_investment_amount_endpoint(): void
    {
        $investor = Investor::factory()->create();
        Investment::factory()->create([
            'investor_id' => $investor->id,
            'amount' => 200,
            'investment_date' => '2024-01-01',
        ]);
        Investment::factory()->create([
            'investor_id' => $investor->id,
            'amount' => 400,
            'investment_date' => '2024-02-01',
        ]);

        $this->getJson('/api/v1/metrics/average-investment-amount')
            ->assertOk()
            ->assertJson(['average_investment_amount' => 300]);
    }

    public function test_total_investments_endpoint(): void
    {
        $investor = Investor::factory()->create();
        Investment::factory()->create([
            'investor_id' => $investor->id,
            'investment_date' => '2024-01-01',
        ]);
        Investment::factory()->create([
            'investor_id' => $investor->id,
            'investment_date' => '2024-02-01',
        ]);

        $this->getJson('/api/v1/metrics/total-investments')
            ->assertOk()
            ->assertJson(['total_investments' => 2]);
    }
}
