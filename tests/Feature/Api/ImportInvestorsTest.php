<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportInvestorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_csv_via_api(): void
    {
        $file = new UploadedFile(
            base_path('tests/fixtures/investors_sample.csv'),
            'investors_sample.csv',
            'text/csv',
            null,
            true,
        );

        $response = $this->postJson('/api/v1/import', [
            'file' => $file,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.investors_upserted', 3)
            ->assertJsonPath('data.investments_upserted', 3);

        $this->assertDatabaseCount('investors', 3);
        $this->assertDatabaseCount('investments', 3);
    }

    public function test_it_validates_file_is_required(): void
    {
        $response = $this->postJson('/api/v1/import', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }
}
