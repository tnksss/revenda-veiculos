<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum VehicleStatus: string implements HasColor, HasLabel
{
    case Preparation = 'preparation';
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';

    public function getLabel(): string
    {
        return match ($this) {
            self::Preparation => 'Em Preparação',
            self::Available => 'Disponível',
            self::Reserved => 'Reservado',
            self::Sold => 'Vendido',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Preparation => 'warning',
            self::Available => 'success',
            self::Reserved => 'info',
            self::Sold => 'gray',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this, [self::Available, self::Reserved], true);
    }
}