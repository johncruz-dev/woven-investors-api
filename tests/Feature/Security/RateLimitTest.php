<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rate_limits_read_requests(): void
    {
        Config::set('security.rate_limits.read', 2);

        $this->getJson('/api/v1/metrics/average-age')->assertOk();
        $this->getJson('/api/v1/metrics/average-age')->assertOk();

        $response = $this->getJson('/api/v1/metrics/average-age');

        $response->assertStatus(429);
    }
}
