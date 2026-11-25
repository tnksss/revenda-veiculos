<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class VehicleObserver
{
    public function updated(Vehicle $vehicle): void
    {
        if ($vehicle->wasChanged('status')) {
            $rawOld = $vehicle->getOriginal('status');
            $oldStatus = $rawOld instanceof VehicleStatus
                ? $rawOld
                : VehicleStatus::tryFrom((string) $rawOld);

            $vehicle->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'status_changed',
                'old_value' => $oldStatus?->getLabel() ?? (string) $rawOld,
                'new_value' => $vehicle->status->getLabel(),
            ]);
        }

        if ($vehicle->wasChanged('price')) {
            $oldPrice = number_format((float) $vehicle->getOriginal('price'), 2, ',', '.');
            $newPrice = number_format((float) $vehicle->price, 2, ',', '.');

            $vehicle->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'price_updated',
                'old_value' => "R$ {$oldPrice}",
                'new_value' => "R$ {$newPrice}",
            ]);
        }
    }
}