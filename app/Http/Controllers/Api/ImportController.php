<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportInvestorsRequest;
use App\Services\CsvImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function __construct(
        private readonly CsvImportService $csvImportService,
    ) {}

    public function store(ImportInvestorsRequest $request): JsonResponse
    {
        $result = $this->csvImportService->import($request->file('file'));

        return response()->json([
            'message' => 'Import completed successfully.',
            'data' => $result,
        ], 201);
    }
}
