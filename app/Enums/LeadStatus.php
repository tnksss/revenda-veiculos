<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case Contacted = 'contacted';
    case Scheduled = 'scheduled';
    case Converted = 'converted';
    case Lost = 'lost';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Novo',
            self::Contacted => 'Em Atendimento',
            self::Scheduled => 'Visita Agendada',
            self::Converted => 'Convertido',
            self::Lost => 'Perdido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Contacted => 'info',
            self::Scheduled => 'primary',
            self::Converted => 'success',
            self::Lost => 'gray',
        };
    }
}
