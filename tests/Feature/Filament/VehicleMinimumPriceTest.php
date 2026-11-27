<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Enums\VehicleStatus;
use App\Filament\Resources\VehicleResource\Pages\EditVehicle;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('minimum_price nunca e exposto na serializacao publica nem na vitrine', function () {
    $vehicle = Vehicle::factory()->create([
        'brand' => 'Honda',
        'model' => 'Civic',
        'price' => 95000.00,
        'minimum_price' => 91500.00,
        'status' => VehicleStatus::Available,
    ]);

    expect($vehicle->toArray())->not->toHaveKey('minimum_price');

    $this->get(route('catalog.show', $vehicle->slug))
        ->assertOk()
        ->assertDontSee('91.500')
        ->assertDontSee('91500');
});

test('vendedor consulta piso de negociacao pelo modal e registra log silencioso de auditoria', function () {
    $seller = User::factory()->create([
        'name' => 'Lucas Vendedor',
        'role' => UserRole::Seller,
        'is_active' => true,
    ]);

    $vehicle = Vehicle::factory()->create([
        'brand' => 'Toyota',
        'model' => 'Corolla',
        'price' => 110000.00,
        'minimum_price' => 105500.00,
        'status' => VehicleStatus::Available,
    ]);

    $this->actingAs($seller);

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->assertFormFieldIsHidden('minimum_price')
        ->mountAction('viewMinimumPrice')
        ->assertSee('R$ 105.500,00')
        ->assertSee('até R$ 4.500,00');

    expect($vehicle->logs()->where('action', 'minimum_price_consulted')->count())->toBe(1)
        ->and($vehicle->logs()->where('action', 'minimum_price_consulted')->first()->notes)
        ->toContain('Lucas Vendedor', 'R$ 105.500,00');
});

test('admin visualiza e edita o campo minimum_price no formulario do veiculo', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);

    $vehicle = Vehicle::factory()->create([
        'price' => 80000.00,
        'minimum_price' => 76000.00,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->assertFormFieldIsVisible('minimum_price');
});
