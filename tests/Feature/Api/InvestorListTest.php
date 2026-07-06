<?php

namespace Tests\Feature\Api;

use App\Models\Investment;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorListTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_paginated_investors_with_totals(): void
    {
        $investor = Investor::factory()->create([
            'external_id' => 1001,
            'name' => 'Daniel Nelson',
            'age' => 28,
        ]);

        Investment::factory()->create([
            'investor_id' => $investor->id,
            'amount' => 100,
            'investment_date' => '2024-01-01',
        ]);
        Investment::factory()->create([
            'investor_id' => $investor->id,
            'amount' => 50,
            'investment_date' => '2024-02-01',
        ]);

        $response = $this->getJson('/api/v1/investors');

        $response->assertOk()
            ->assertJsonPath('data.0.investor_id', 1001)
            ->assertJsonPath('data.0.name', 'Daniel Nelson')
            ->assertJsonPath('data.0.age', 28)
            ->assertJsonPath('data.0.investment_amount', 150)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total', 'last_page']]);
    }

    public function test_it_exports_investors_as_csv(): void
    {
        $investor = Investor::factory()->create([
            'external_id' => 1001,
            'name' => 'Daniel Nelson',
            'age' => 28,
        ]);

        Investment::factory()->create([
            'investor_id' => $investor->id,
            'amount' => 100,
            'investment_date' => '2024-01-01',
        ]);

        $response = $this->get('/api/v1/investors?format=csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Daniel Nelson', $response->streamedContent());
    }
}
