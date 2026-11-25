<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TransmissionType: string implements HasLabel
{
    case Manual = 'manual';
    case Automatic = 'automatic';
    case Automated = 'automated';
    case Cvt = 'cvt';

    public function getLabel(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Automatic => 'Automático',
            self::Automated => 'Automatizado',
            self::Cvt => 'CVT',
        };
    }
}