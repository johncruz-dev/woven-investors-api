<?php

namespace App\Repositories;

use App\Models\Investor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InvestorRepository
{
    public function upsertBatch(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $now = now();

        $payload = collect($rows)->map(fn (array $row) => [
            'external_id' => $row['external_id'],
            'name' => $row['name'],
            'age' => $row['age'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->unique('external_id')->values()->all();

        Investor::upsert($payload, ['external_id'], ['name', 'age', 'updated_at']);
    }

    public function getIdMapByExternalIds(array $externalIds): Collection
    {
        return Investor::query()
            ->whereIn('external_id', $externalIds)
            ->pluck('id', 'external_id');
    }

    public function getAverageAge(): ?float
    {
        return Investor::query()->avg('age');
    }

    public function paginateWithInvestmentTotals(int $perPage): LengthAwarePaginator
    {
        return Investor::query()
            ->withSum('investments', 'amount')
            ->orderBy('external_id')
            ->paginate($perPage);
    }

    public function allWithInvestmentTotals(): Collection
    {
        return Investor::query()
            ->withSum('investments', 'amount')
            ->orderBy('external_id')
            ->get();
    }
}
