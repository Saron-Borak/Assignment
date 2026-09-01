<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Scheduled = 'scheduled';
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Scheduled => 'text-bg-secondary',
            self::Open => 'text-bg-success',
            self::Closed => 'text-bg-dark',
        };
    }
}
