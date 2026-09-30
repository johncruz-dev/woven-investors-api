<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('security.auth_required', true);
        Config::set('security.registration_enabled', true);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in')
            ->assertSee('Create one');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_authenticate_with_remember_me(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'password',
            'remember' => 'on',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_cannot_authenticate_with_unknown_email(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'missing@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->from(route('login'))->post(route('login'), []);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_users_are_redirected_away_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_logout(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_login_redirects_to_intended_url(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->get('/');

        $response = $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_dashboard_requires_authentication_when_auth_is_required(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_viewers_can_view_read_only_dashboard(): void
    {
        $user = User::factory()->viewer()->create([
            'name' => 'Viewer Person',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Investors Dashboard')
            ->assertSee('Viewer Person')
            ->assertSee('Viewer')
            ->assertSee('Read-only access')
            ->assertSee('Sign out')
            ->assertDontSee('id="import-form"', false)
            ->assertDontSee('Upload & Import');
    }

    public function test_authenticated_admins_see_import_controls(): void
    {
        $user = User::factory()->admin()->create([
            'name' => 'Admin Person',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Admin Person')
            ->assertSee('Admin')
            ->assertSee('Import CSV')
            ->assertDontSee('Read-only access');
    }

    public function test_dashboard_is_public_when_auth_is_not_required(): void
    {
        Config::set('security.auth_required', false);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Investors Dashboard')
            ->assertSee('Import CSV')
            ->assertDontSee('Sign out');
    }
}
