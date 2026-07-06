<?php

namespace App\Repositories;

use App\Models\Investment;

class InvestmentRepository
{
    public function upsertBatch(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $now = now();

        $payload = collect($rows)->map(fn (array $row) => [
            'investor_id' => $row['investor_id'],
            'amount' => $row['amount'],
            'investment_date' => $row['investment_date'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->unique(fn (array $row) => $row['investor_id'].'|'.$row['investment_date'])->values()->all();

        Investment::upsert(
            $payload,
            ['investor_id', 'investment_date'],
            ['amount', 'updated_at']
        );
    }

    public function getAverageAmount(): ?float
    {
        return Investment::query()->avg('amount');
    }

    public function getTotalCount(): int
    {
        return Investment::query()->count();
    }
}
