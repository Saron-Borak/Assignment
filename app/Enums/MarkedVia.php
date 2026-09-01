<?php

namespace App\Enums;

enum MarkedVia: string
{
    case Manual = 'manual';
    case Qr = 'qr';
    case Code = 'code';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Marked by lecturer',
            self::Qr => 'QR self check-in',
            self::Code => 'Code self check-in',
            self::System => 'Auto-marked on close',
        };
    }
}
