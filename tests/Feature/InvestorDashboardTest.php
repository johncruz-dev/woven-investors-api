<?php

namespace Tests\Feature;

use Tests\TestCase;

class InvestorDashboardTest extends TestCase
{
    public function test_dashboard_page_loads(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Investors Dashboard')
            ->assertSee('Import CSV');
    }
}
