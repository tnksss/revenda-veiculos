<?php

declare(strict_types=1);

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Actions\ViewVehicleMinimumPriceAction;
use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewVehicleMinimumPriceAction::make(
                resolveVehicle: fn (): ?Vehicle => $this->record instanceof Vehicle ? $this->record : null,
                context: 'Ficha do Veículo',
            ),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record instanceof Vehicle) {
            $data['plate'] = $this->record->plate;
        }

        return $data;
    }
}
