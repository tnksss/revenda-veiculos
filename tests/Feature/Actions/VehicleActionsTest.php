<?php

declare(strict_types=1);

use App\Actions\Vehicles\MarkVehicleAsSoldAction;
use App\Actions\Vehicles\PublishVehicleAction;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;

test('MarkVehicleAsSoldAction atualiza status para vendido e remove destaque', function (): void {
    $vehicle = Vehicle::factory()->available()->create([
        'is_featured' => true,
    ]);

    app(MarkVehicleAsSoldAction::class)->execute($vehicle);

    $vehicle->refresh();

    expect($vehicle->status)->toBe(VehicleStatus::Sold)
        ->and($vehicle->is_featured)->toBeFalse();
});

test('PublishVehicleAction publica veiculo quando possui 3 ou mais fotos', function (): void {
    $vehicle = Vehicle::factory()->preparation()->create();

    $vehicle->photos()->createMany([
        ['path' => 'vehicles/1.jpg', 'sort_order' => 0],
        ['path' => 'vehicles/2.jpg', 'sort_order' => 1],
        ['path' => 'vehicles/3.jpg', 'sort_order' => 2],
    ]);

    app(PublishVehicleAction::class)->execute($vehicle);

    expect($vehicle->fresh()->status)->toBe(VehicleStatus::Available);
});

test('PublishVehicleAction lanca excecao quando veiculo possui menos de 3 fotos', function (): void {
    $vehicle = Vehicle::factory()->preparation()->create();

    $vehicle->photos()->createMany([
        ['path' => 'vehicles/1.jpg', 'sort_order' => 0],
        ['path' => 'vehicles/2.jpg', 'sort_order' => 1],
    ]);

    expect(fn () => app(PublishVehicleAction::class)->execute($vehicle))
        ->toThrow(DomainException::class, 'Para publicar o veículo como Disponível, adicione pelo menos 3 fotos.');

    expect($vehicle->fresh()->status)->toBe(VehicleStatus::Preparation);
});
