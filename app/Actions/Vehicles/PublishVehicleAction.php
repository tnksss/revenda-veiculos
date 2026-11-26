<?php

declare(strict_types=1);

namespace App\Actions\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use DomainException;

final readonly class PublishVehicleAction
{
    public function execute(Vehicle $vehicle): Vehicle
    {
        if ($vehicle->photos()->count() < 3) {
            throw new DomainException('Para publicar o veículo como Disponível, adicione pelo menos 3 fotos.');
        }

        $vehicle->update([
            'status' => VehicleStatus::Available,
        ]);

        return $vehicle;
    }
}
