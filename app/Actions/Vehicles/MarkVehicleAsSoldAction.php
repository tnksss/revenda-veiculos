<?php

declare(strict_types=1);

namespace App\Actions\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;

final readonly class MarkVehicleAsSoldAction
{
    public function execute(Vehicle $vehicle): Vehicle
    {
        if ($vehicle->status === VehicleStatus::Sold) {
            return $vehicle;
        }

        $vehicle->update([
            'status' => VehicleStatus::Sold,
            'is_featured' => false,
        ]);

        return $vehicle;
    }
}
