<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Canceled = 'canceled';
    case PastDue = 'past_due';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Canceled => 'Canceled',
            self::PastDue => 'Past due',
            self::Expired => 'Expired',
        };
    }
}
