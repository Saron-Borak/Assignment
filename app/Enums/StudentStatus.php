<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Graduated = 'graduated';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'text-bg-success',
            self::Inactive => 'text-bg-secondary',
            self::Graduated => 'text-bg-primary',
        };
    }
}
