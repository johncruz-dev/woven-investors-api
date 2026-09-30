<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Viewer => 'Viewer',
        };
    }

    public function canImport(): bool
    {
        return $this === self::Admin;
    }

    public function canRead(): bool
    {
        return true;
    }
}
