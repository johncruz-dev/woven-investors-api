<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('security.auth_required', true);
    }

    public function test_unauthenticated_users_cannot_access_read_endpoints(): void
    {
        $this->getJson('/api/v1/metrics/average-age')->assertUnauthorized();
        $this->getJson('/api/v1/metrics/average-investment-amount')->assertUnauthorized();
        $this->getJson('/api/v1/metrics/total-investments')->assertUnauthorized();
        $this->getJson('/api/v1/investors')->assertUnauthorized();
        $this->get('/api/v1/investors?format=csv')->assertUnauthorized();
    }

    public function test_unauthenticated_users_cannot_import(): void
    {
        $file = $this->sampleCsv();

        $this->postJson('/api/v1/import', ['file' => $file])
            ->assertUnauthorized();
    }

    public function test_viewer_can_access_all_read_endpoints(): void
    {
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->getJson('/api/v1/metrics/average-age')->assertOk();
        $this->actingAs($viewer)->getJson('/api/v1/metrics/average-investment-amount')->assertOk();
        $this->actingAs($viewer)->getJson('/api/v1/metrics/total-investments')->assertOk();
        $this->actingAs($viewer)->getJson('/api/v1/investors')->assertOk();
        $this->actingAs($viewer)->get('/api/v1/investors?format=csv')->assertOk();
    }

    public function test_viewer_cannot_import(): void
    {
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)
            ->postJson('/api/v1/import', ['file' => $this->sampleCsv()])
            ->assertForbidden()
            ->assertJsonPath('message', 'This action is unauthorized.');
    }

    public function test_admin_can_access_all_read_endpoints(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/api/v1/metrics/average-age')->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/metrics/average-investment-amount')->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/metrics/total-investments')->assertOk();
        $this->actingAs($admin)->getJson('/api/v1/investors')->assertOk();
        $this->actingAs($admin)->get('/api/v1/investors?format=csv')->assertOk();
    }

    public function test_admin_can_import(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->postJson('/api/v1/import', ['file' => $this->sampleCsv()])
            ->assertCreated()
            ->assertJsonPath('data.investors_upserted', 3);
    }

    public function test_sanctum_viewer_token_cannot_import(): void
    {
        $viewer = User::factory()->viewer()->create();
        $token = $viewer->createToken('viewer-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/import', ['file' => $this->sampleCsv()])
            ->assertForbidden();
    }

    public function test_sanctum_admin_token_can_import_and_read(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/metrics/average-age')
            ->assertOk();

        $this->withToken($token)
            ->postJson('/api/v1/import', ['file' => $this->sampleCsv()])
            ->assertCreated();
    }

    public function test_api_key_can_import_without_user_role(): void
    {
        Config::set('security.api.key', 'machine-key');

        $this->withHeaders(['X-Api-Key' => 'machine-key'])
            ->postJson('/api/v1/import', ['file' => $this->sampleCsv()])
            ->assertCreated();
    }

    public function test_api_is_open_when_auth_is_not_required(): void
    {
        Config::set('security.auth_required', false);

        $this->getJson('/api/v1/metrics/average-age')->assertOk();

        $this->postJson('/api/v1/import', ['file' => $this->sampleCsv()])
            ->assertCreated();
    }

    private function sampleCsv(): UploadedFile
    {
        return new UploadedFile(
            base_path('tests/fixtures/investors_sample.csv'),
            'investors_sample.csv',
            'text/csv',
            null,
            true,
        );
    }
}
