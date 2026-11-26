<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\VehiclePhoto;
use Illuminate\Support\Facades\Storage;

class VehiclePhotoObserver
{
    public function deleted(VehiclePhoto $photo): void
    {
        if (! empty($photo->path) && Storage::disk('public')->exists($photo->path)) {
            Storage::disk('public')->delete($photo->path);
        }
    }
}
