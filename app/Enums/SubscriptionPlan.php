<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case Standard = 'standard';
    case Premium = 'premium';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Premium => 'Premium',
            self::Enterprise => 'Enterprise',
        };
    }

    public function monthlyAmount(): float
    {
        return match ($this) {
            self::Standard => 29.00,
            self::Premium => 49.99,
            self::Enterprise => 99.00,
        };
    }

    public function yearlyAmount(): float
    {
        return round($this->monthlyAmount() * 10, 2);
    }
}
