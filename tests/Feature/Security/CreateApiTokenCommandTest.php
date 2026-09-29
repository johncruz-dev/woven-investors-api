<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateApiTokenCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_sanctum_token_for_a_user(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->artisan('security:create-api-token', [
            'email' => 'admin@example.com',
            'name' => 'cli-token',
        ])->assertSuccessful();

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'cli-token',
        ]);
    }
}
