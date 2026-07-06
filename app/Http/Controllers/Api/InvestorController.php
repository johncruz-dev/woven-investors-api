<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\InvestorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvestorController extends Controller
{
    public function __construct(
        private readonly InvestorRepository $investorRepository,
    ) {}

    public function index(Request $request): JsonResponse|StreamedResponse
    {
        if ($request->query('format') === 'csv') {
            return $this->exportCsv();
        }

        $perPage = min((int) $request->query('per_page', 50), 200);
        $paginator = $this->investorRepository->paginateWithInvestmentTotals($perPage);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn ($investor) => $this->transformInvestor($investor)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    private function exportCsv(): StreamedResponse
    {
        $investors = $this->investorRepository->allWithInvestmentTotals();

        return response()->streamDownload(function () use ($investors) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['investor_id', 'name', 'age', 'investment_amount']);

            foreach ($investors as $investor) {
                fputcsv($handle, [
                    $investor->external_id,
                    $investor->name,
                    $investor->age,
                    $investor->investments_sum_amount ?? 0,
                ]);
            }

            fclose($handle);
        }, 'investors.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function transformInvestor($investor): array
    {
        return [
            'investor_id' => $investor->external_id,
            'name' => $investor->name,
            'age' => $investor->age,
            'investment_amount' => round((float) ($investor->investments_sum_amount ?? 0), 2),
        ];
    }
}
