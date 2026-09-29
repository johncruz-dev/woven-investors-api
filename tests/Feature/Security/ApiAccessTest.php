<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ApiAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('security.api.auth_required', true);
        Config::set('security.api.key', 'test-api-key');
    }

    public function test_it_rejects_unauthenticated_api_requests_when_auth_is_required(): void
    {
        $response = $this->getJson('/api/v1/metrics/average-age');

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_it_accepts_valid_api_key(): void
    {
        $response = $this->withHeaders([
            'X-Api-Key' => 'test-api-key',
        ])->getJson('/api/v1/metrics/average-age');

        $response->assertOk()
            ->assertJsonStructure(['average_age']);
    }

    public function test_it_rejects_invalid_api_key(): void
    {
        $response = $this->withHeaders([
            'X-Api-Key' => 'wrong-key',
        ])->getJson('/api/v1/metrics/average-age');

        $response->assertUnauthorized();
    }

    public function test_it_accepts_valid_sanctum_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/metrics/average-age');

        $response->assertOk()
            ->assertJsonStructure(['average_age']);
    }
}
