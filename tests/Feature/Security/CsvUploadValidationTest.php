<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_binary_uploads(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, "\0\x01\x02invalid");

        $file = new UploadedFile($path, 'bad.csv', 'text/csv', null, true);

        $response = $this->postJson('/api/v1/import', [
            'file' => $file,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_it_rejects_script_content_in_uploads(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, '<?php echo "hack"; ?>');

        $file = new UploadedFile($path, 'bad.csv', 'text/csv', null, true);

        $response = $this->postJson('/api/v1/import', [
            'file' => $file,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }
}
