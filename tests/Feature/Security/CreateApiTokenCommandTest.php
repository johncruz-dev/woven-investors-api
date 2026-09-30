<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class CreateApiTokenCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_sanctum_token_for_a_user(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $this->artisan('security:create-api-token', [
            'email' => 'admin@example.com',
            'name' => 'cli-token',
        ])
            ->expectsOutputToContain('API token created successfully.')
            ->assertSuccessful();

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'cli-token',
        ]);
    }

    public function test_it_creates_token_with_custom_abilities(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $this->artisan('security:create-api-token', [
            'email' => 'admin@example.com',
            'name' => 'read-only',
            '--abilities' => ['read'],
        ])->assertSuccessful();

        $token = PersonalAccessToken::query()
            ->where('tokenable_id', $user->id)
            ->where('name', 'read-only')
            ->first();

        $this->assertNotNull($token);
        $this->assertSame(['read'], $token->abilities);
    }

    public function test_it_fails_when_user_email_is_unknown(): void
    {
        $this->artisan('security:create-api-token', [
            'email' => 'missing@example.com',
        ])
            ->expectsOutputToContain('No user found with that email address.')
            ->assertFailed();
    }
}
