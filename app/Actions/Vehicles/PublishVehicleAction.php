<?php

declare(strict_types=1);

namespace App\Actions\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Validation\ValidationException;

final class PublishVehicleAction
{
    public function execute(Vehicle $vehicle): Vehicle
    {
        if ($vehicle->getMedia('gallery')->count() < 3) {
            throw ValidationException::withMessages([
                'status' => 'O veículo precisa ter pelo menos 3 fotos na galeria para ser publicado.',
            ]);
        }

        $vehicle->update([
            'status' => VehicleStatus::Available,
        ]);

        return $vehicle->refresh();
    }
}
