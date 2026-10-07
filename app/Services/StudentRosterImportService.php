<?php

namespace App\Services;

use App\Models\School;
use App\Models\Student;
use App\Rules\ValidCsvUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use RuntimeException;
use SplFileObject;

class StudentRosterImportService
{
    private const CHUNK_SIZE = 200;

    private const EXPECTED_HEADERS = [
        'student_id',
        'name',
        'email',
        'grade_level',
    ];

    public function import(School $school, UploadedFile $file): array
    {
        Validator::make(
            ['file' => $file],
            ['file' => ['required', 'file', 'mimes:csv,txt', 'max:'.config('security.upload.max_size_kb', 10240), new ValidCsvUpload]]
        )->validate();

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

        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);

        if ($headers !== self::EXPECTED_HEADERS) {
            throw new InvalidArgumentException(
                'Invalid CSV headers. Expected: '.implode(', ', self::EXPECTED_HEADERS)
            );
        }

        $upserted = 0;
        $chunk = [];
        $maxRows = config('security.upload.max_rows', 50000);

        while (! $fileObject->eof()) {
            $row = $fileObject->fgetcsv();

            if ($row === false || $row === [null]) {
                continue;
            }

            $upserted++;

            if ($upserted > $maxRows) {
                throw new InvalidArgumentException(
                    "CSV exceeds the maximum allowed row count of {$maxRows}."
                );
            }

            $chunk[] = $this->parseRow($row);

            if (count($chunk) >= self::CHUNK_SIZE) {
                $this->persistChunk($school, $chunk);
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            $this->persistChunk($school, $chunk);
        }

        return [
            'students_upserted' => $upserted,
        ];
    }

    private function parseRow(array $row): array
    {
        if (count($row) !== count(self::EXPECTED_HEADERS)) {
            throw new InvalidArgumentException('CSV row has an invalid number of columns.');
        }

        [$externalId, $name, $email, $grade] = $row;

        $externalId = trim((string) $externalId);
        $name = trim((string) $name);
        $email = trim((string) $email);
        $grade = trim((string) $grade);

        if ($externalId === '' || $name === '') {
            throw new InvalidArgumentException('Each roster row requires student_id and name.');
        }

        return [
            'external_id' => $externalId,
            'name' => $name,
            'email' => $email !== '' ? $email : null,
            'grade_level' => $grade !== '' ? $grade : null,
            'status' => 'active',
        ];
    }

    private function persistChunk(School $school, array $chunk): void
    {
        $now = now();

        $payload = collect($chunk)->map(fn (array $row) => [
            'school_id' => $school->id,
            'external_id' => $row['external_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'grade_level' => $row['grade_level'],
            'status' => $row['status'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->unique('external_id')->values()->all();

        Student::upsert(
            $payload,
            ['school_id', 'external_id'],
            ['name', 'email', 'grade_level', 'status', 'updated_at']
        );
    }
}
