<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\VehicleFilterRequest;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class VehicleCatalogController extends Controller
{
    public function index(VehicleFilterRequest $request): View
    {
        $filters = $request->validated();

        $query = Vehicle::query()
            ->publiclyVisible()
            ->with('media');

        if (! empty($filters['q'])) {
            $term = mb_strtolower(trim((string) $filters['q']));
            $query->where(function (Builder $subQuery) use ($term): void {
                $subQuery->whereRaw('LOWER(brand) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(model) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(version) LIKE ?', ["%{$term}%"]);
            });
        }

        if (! empty($filters['fuel'])) {
            $query->where('fuel_type', $filters['fuel']);
        }

        if (! empty($filters['transmission'])) {
            $query->where('transmission', $filters['transmission']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        if (! empty($filters['min_year'])) {
            $query->where('year_model', '>=', (int) $filters['min_year']);
        }

        match ($filters['sort'] ?? 'featured') {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderByDesc('year_model')->orderByDesc('created_at'),
            default => $query->orderByDesc('is_featured')->orderByDesc('created_at'),
        };

        $vehicles = $query->paginate(12)->withQueryString();

        return view('catalog.index', [
            'vehicles' => $vehicles,
            'filters' => $filters,
            'fuelTypes' => FuelType::cases(),
            'transmissionTypes' => TransmissionType::cases(),
        ]);
    }

    public function show(string $slug): View
    {
        $vehicle = Vehicle::query()
            ->publiclyVisible()
            ->with(['media'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedVehicles = Vehicle::query()
            ->publiclyVisible()
            ->with('media')
            ->where('id', '!=', $vehicle->id)
            ->orderByDesc('is_featured')
            ->limit(3)
            ->get();

        return view('catalog.show', [
            'vehicle' => $vehicle,
            'relatedVehicles' => $relatedVehicles,
        ]);
    }
}
