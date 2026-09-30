<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ApiAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('security.auth_required', true);
        Config::set('security.api.key', 'test-api-key');
        Config::set('security.api.allow_sanctum_tokens', true);
        Config::set('security.api.log_failed_attempts', true);
    }

    public function test_it_rejects_unauthenticated_api_requests_when_auth_is_required(): void
    {
        $this->getJson('/api/v1/metrics/average-age')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_it_logs_failed_authentication_attempts(): void
    {
        Log::spy();

        $this->getJson('/api/v1/metrics/average-age')->assertUnauthorized();

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message) => $message === 'API authentication failed.')
            ->once();
    }

    public function test_it_accepts_valid_api_key(): void
    {
        $this->withHeaders(['X-Api-Key' => 'test-api-key'])
            ->getJson('/api/v1/metrics/average-age')
            ->assertOk()
            ->assertJsonStructure(['average_age']);
    }

    public function test_it_rejects_invalid_api_key(): void
    {
        $this->withHeaders(['X-Api-Key' => 'wrong-key'])
            ->getJson('/api/v1/metrics/average-age')
            ->assertUnauthorized();
    }

    public function test_it_rejects_empty_api_key_header(): void
    {
        $this->withHeaders(['X-Api-Key' => ''])
            ->getJson('/api/v1/metrics/average-age')
            ->assertUnauthorized();
    }

    public function test_it_accepts_valid_sanctum_token(): void
    {
        $user = User::factory()->viewer()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/v1/metrics/average-age')
            ->assertOk()
            ->assertJsonStructure(['average_age']);
    }

    public function test_it_rejects_invalid_sanctum_token(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid-token'])
            ->getJson('/api/v1/metrics/average-age')
            ->assertUnauthorized();
    }

    public function test_it_rejects_sanctum_tokens_when_disabled(): void
    {
        Config::set('security.api.allow_sanctum_tokens', false);

        $user = User::factory()->viewer()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/v1/metrics/average-age')
            ->assertUnauthorized();
    }

    public function test_it_accepts_session_authenticated_users(): void
    {
        $user = User::factory()->viewer()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/metrics/average-age')
            ->assertOk();
    }

    public function test_it_allows_open_access_when_auth_is_not_required(): void
    {
        Config::set('security.auth_required', false);

        $this->getJson('/api/v1/metrics/average-age')->assertOk();
    }

    public function test_api_key_takes_precedence_and_allows_access_without_user(): void
    {
        $this->withHeaders(['X-Api-Key' => 'test-api-key'])
            ->getJson('/api/v1/investors')
            ->assertOk();
    }
}
