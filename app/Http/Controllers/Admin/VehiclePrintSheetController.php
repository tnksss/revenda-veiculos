<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;

class VehiclePrintSheetController extends Controller
{
    public function __invoke(Vehicle $vehicle): View
    {
        return view('admin.vehicles.print-sheet', [
            'vehicle' => $vehicle,
            'publicUrl' => route('catalog.show', $vehicle->slug),
        ]);
    }
}
