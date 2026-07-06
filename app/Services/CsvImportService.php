<?php

namespace App\Services;

use App\Repositories\InvestmentRepository;
use App\Repositories\InvestorRepository;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use RuntimeException;
use SplFileObject;

class CsvImportService
{
    private const CHUNK_SIZE = 500;

    private const EXPECTED_HEADERS = [
        'investor_id',
        'name',
        'age',
        'investment_amount',
        'investment_date',
    ];

    public function __construct(
        private readonly InvestorRepository $investorRepository,
        private readonly InvestmentRepository $investmentRepository,
    ) {}

    public function import(UploadedFile $file): array
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read uploaded file.');
        }

        $fileObject = new SplFileObject($path, 'r');
        $fileObject->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = $fileObject->fgetcsv();

        if ($headers === false) {
            throw new InvalidArgumentException('CSV file is empty.');
        }

        $headers = array_map('trim', $headers);

        if ($headers !== self::EXPECTED_HEADERS) {
            throw new InvalidArgumentException(
                'Invalid CSV headers. Expected: '.implode(', ', self::EXPECTED_HEADERS)
            );
        }

        $importedInvestors = 0;
        $importedInvestments = 0;
        $chunk = [];

        while (! $fileObject->eof()) {
            $row = $fileObject->fgetcsv();

            if ($row === false || $row === [null]) {
                continue;
            }

            $chunk[] = $this->parseRow($row);
            $importedInvestments++;

            if (count($chunk) >= self::CHUNK_SIZE) {
                $importedInvestors += $this->persistChunk($chunk);
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            $importedInvestors += $this->persistChunk($chunk);
        }

        return [
            'investors_upserted' => $importedInvestors,
            'investments_upserted' => $importedInvestments,
        ];
    }

    private function parseRow(array $row): array
    {
        if (count($row) !== count(self::EXPECTED_HEADERS)) {
            throw new InvalidArgumentException('CSV row has an invalid number of columns.');
        }

        [$externalId, $name, $age, $amount, $date] = $row;

        try {
            $parsedDate = Carbon::createFromFormat('d-m-Y', trim($date));
        } catch (InvalidFormatException) {
            throw new InvalidArgumentException("Invalid investment date format: {$date}");
        }

        return [
            'external_id' => (int) trim($externalId),
            'name' => trim($name),
            'age' => (int) trim($age),
            'amount' => (float) trim($amount),
            'investment_date' => $parsedDate->toDateString(),
        ];
    }

    private function persistChunk(array $chunk): int
    {
        $this->investorRepository->upsertBatch($chunk);

        $externalIds = collect($chunk)->pluck('external_id')->unique()->all();
        $idMap = $this->investorRepository->getIdMapByExternalIds($externalIds);

        $investmentRows = collect($chunk)->map(function (array $row) use ($idMap) {
            return [
                'investor_id' => $idMap[$row['external_id']],
                'amount' => $row['amount'],
                'investment_date' => $row['investment_date'],
            ];
        })->all();

        $this->investmentRepository->upsertBatch($investmentRows);

        return count($externalIds);
    }
}
