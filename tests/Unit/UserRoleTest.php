<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_permissions(): void
    {
        $role = UserRole::Admin;

        $this->assertSame('admin', $role->value);
        $this->assertSame('Admin', $role->label());
        $this->assertTrue($role->canImport());
        $this->assertTrue($role->canRead());
    }

    public function test_viewer_role_permissions(): void
    {
        $role = UserRole::Viewer;

        $this->assertSame('viewer', $role->value);
        $this->assertSame('Viewer', $role->label());
        $this->assertFalse($role->canImport());
        $this->assertTrue($role->canRead());
    }

    public function test_user_admin_helpers(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isViewer());
        $this->assertTrue($user->canImport());
        $this->assertTrue($user->canRead());
        $this->assertTrue($user->hasRole(UserRole::Admin));
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('viewer'));
    }

    public function test_user_viewer_helpers(): void
    {
        $user = User::factory()->viewer()->create();

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isViewer());
        $this->assertFalse($user->canImport());
        $this->assertTrue($user->canRead());
        $this->assertTrue($user->hasRole(UserRole::Viewer));
        $this->assertTrue($user->hasRole('viewer'));
        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_factory_defaults_to_viewer(): void
    {
        $user = User::factory()->create();

        $this->assertSame(UserRole::Viewer, $user->role);
    }
}
