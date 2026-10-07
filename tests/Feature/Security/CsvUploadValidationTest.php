<?php

namespace Tests\Feature\Security;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        School::factory()->create([
            'name' => 'Test Academy',
            'slug' => 'test-academy',
        ]);
    }

    public function test_it_rejects_binary_uploads(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, "\0\x01\x02invalid");

        $file = new UploadedFile($path, 'bad.csv', 'text/csv', null, true);

        $this->actingAs($this->admin)
            ->post(route('education.roster.store'), ['file' => $file])
            ->assertSessionHasErrors(['file']);
    }

    public function test_it_rejects_script_content_in_uploads(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, '<?php echo "hack"; ?>');

        $file = new UploadedFile($path, 'bad.csv', 'text/csv', null, true);

        $this->actingAs($this->admin)
            ->post(route('education.roster.store'), ['file' => $file])
            ->assertSessionHasErrors(['file']);
    }
}
