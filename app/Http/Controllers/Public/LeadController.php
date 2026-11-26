<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreLeadRequest;
use App\Models\Lead;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, string $slug): RedirectResponse
    {
        $vehicle = Vehicle::query()
            ->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        $validated = $request->validated();

        Lead::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'has_trade_in' => (bool) ($validated['has_trade_in'] ?? false),
            'wants_financing' => (bool) ($validated['wants_financing'] ?? false),
            'message' => $validated['message'] ?? null,
            'status' => LeadStatus::New,
        ]);

        return redirect()
            ->route('catalog.show', $vehicle->slug)
            ->with('lead_success', 'Proposta enviada com sucesso! Um consultor entrará em contato em breve.');
    }
}
