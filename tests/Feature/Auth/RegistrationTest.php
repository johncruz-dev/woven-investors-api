<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('security.auth_required', true);
        Config::set('security.registration_enabled', true);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create account')
            ->assertSee('viewer role');
    }

    public function test_new_users_can_register_as_viewers(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'new@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Viewer, $user->role);
        $this->assertTrue($user->isViewer());
        $this->assertFalse($user->canImport());
    }

    public function test_registration_cannot_assign_admin_role(): void
    {
        $this->post(route('register'), [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'hacker@example.com',
            'role' => UserRole::Viewer->value,
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'hacker@example.com',
            'role' => UserRole::Admin->value,
        ]);
    }

    public function test_registration_requires_all_fields(): void
    {
        $response = $this->from(route('register'))->post(route('register'), []);

        $this->assertGuest();
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Another User',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'New User',
            'email' => 'not-valid',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_users_are_redirected_away_from_register(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_registration_screen_is_unavailable_when_disabled(): void
    {
        Config::set('security.registration_enabled', false);

        $this->get(route('register'))->assertNotFound();
    }

    public function test_registration_submission_is_unavailable_when_disabled(): void
    {
        Config::set('security.registration_enabled', false);

        $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_login_screen_hides_register_link_when_registration_is_disabled(): void
    {
        Config::set('security.registration_enabled', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Create one');
    }
}
