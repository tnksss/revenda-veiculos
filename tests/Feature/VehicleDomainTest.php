<?php

declare(strict_types=1);

use App\Enums\VehicleStatus;
use App\Models\Vehicle;

test('normaliza placa para maiusculo sem hifen e gera slug unico automaticamente', function () {
    $vehicle = Vehicle::factory()->create([
        'brand' => 'Honda',
        'model' => 'Civic',
        'version' => 'EXL 2.0',
        'year_model' => 2022,
        'plate' => 'abc-1d28',
    ]);

    expect($vehicle->plate)->toBe('ABC1D28')
        ->and($vehicle->plate_ending)->toBe('8')
        ->and($vehicle->slug)->toStartWith('honda-civic-exl-20-2022-');
});

test('oculta placa completa, preco de custo e notas internas na serializacao publica', function () {
    $vehicle = Vehicle::factory()->create([
        'plate' => 'XYZ9A99',
        'cost_price' => 85000.00,
        'internal_notes' => 'Comprado no leilao da seguradora',
    ]);

    $serialized = $vehicle->toArray();

    expect($serialized)
        ->not->toHaveKey('plate')
        ->not->toHaveKey('cost_price')
        ->not->toHaveKey('internal_notes')
        ->and($vehicle->plate_ending)->toBe('9');
});

test('escopo publiclyVisible exibe apenas veiculos disponiveis e reservados', function () {
    $available = Vehicle::factory()->create(['status' => VehicleStatus::Available]);
    $reserved = Vehicle::factory()->create(['status' => VehicleStatus::Reserved]);
    $preparation = Vehicle::factory()->create(['status' => VehicleStatus::Preparation]);
    $sold = Vehicle::factory()->create(['status' => VehicleStatus::Sold]);

    $visibleIds = Vehicle::query()->publiclyVisible()->pluck('id');

    expect($visibleIds)
        ->toContain($available->id)
        ->toContain($reserved->id)
        ->not->toContain($preparation->id)
        ->not->toContain($sold->id);
});

test('observer registra log automatico ao alterar status ou preco do veiculo', function (): void {
    $vehicle = Vehicle::factory()->create([
        'status' => VehicleStatus::Preparation,
        'price' => 95000.00,
    ]);

    $vehicle->update([
        'status' => VehicleStatus::Available,
        'price' => 92500.00,
    ]);

    $this->assertDatabaseHas('vehicle_logs', [
        'vehicle_id' => $vehicle->id,
        'action' => 'status_changed',
        'old_value' => 'Em Preparação',
        'new_value' => 'Disponível',
    ]);

    $this->assertDatabaseHas('vehicle_logs', [
        'vehicle_id' => $vehicle->id,
        'action' => 'price_updated',
        'old_value' => 'R$ 95.000,00',
        'new_value' => 'R$ 92.500,00',
    ]);
});
