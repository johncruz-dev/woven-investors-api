<?php

namespace Tests\Unit\Services;

use App\Repositories\InvestmentRepository;
use App\Repositories\InvestorRepository;
use App\Services\CsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Tests\TestCase;

class CsvImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private CsvImportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CsvImportService(
            new InvestorRepository,
            new InvestmentRepository,
        );
    }

    public function test_it_imports_valid_csv_rows(): void
    {
        $file = new UploadedFile(
            base_path('tests/fixtures/investors_sample.csv'),
            'investors_sample.csv',
            'text/csv',
            null,
            true,
        );

        $result = $this->service->import($file);

        $this->assertSame(3, $result['investors_upserted']);
        $this->assertSame(3, $result['investments_upserted']);
        $this->assertDatabaseCount('investors', 3);
        $this->assertDatabaseCount('investments', 3);
        $this->assertDatabaseHas('investors', [
            'external_id' => 1001,
            'name' => 'Daniel Nelson',
            'age' => 28,
        ]);
    }

    public function test_it_rejects_invalid_headers(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, "id,name\n1,Test\n");

        $file = new UploadedFile($path, 'invalid.csv', 'text/csv', null, true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid CSV headers');

        $this->service->import($file);
    }
}
