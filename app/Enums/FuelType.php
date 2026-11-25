<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FuelType: string implements HasLabel
{
    case Flex = 'flex';
    case Gasoline = 'gasoline';
    case Ethanol = 'ethanol';
    case Diesel = 'diesel';
    case Hybrid = 'hybrid';
    case Electric = 'electric';

    public function getLabel(): string
    {
        return match ($this) {
            self::Flex => 'Flex (Gasolina/Etanol)',
            self::Gasoline => 'Gasolina',
            self::Ethanol => 'Etanol',
            self::Diesel => 'Diesel',
            self::Hybrid => 'Híbrido',
            self::Electric => 'Elétrico',
        };
    }
}
