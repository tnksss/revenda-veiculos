<?php

declare(strict_types=1);

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use App\Filament\Resources\VehicleResource\Pages\CreateVehicle;
use App\Filament\Resources\VehicleResource\Pages\EditVehicle;
use App\Filament\Resources\VehicleResource\Pages\ListVehicles;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

test('renderiza a listagem de veiculos no painel administrativo', function (): void {
    $vehicles = Vehicle::factory()->count(3)->create();

    Livewire::test(ListVehicles::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($vehicles);
});

test('cria um novo veiculo em preparacao sem exigir fotos', function (): void {
    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'version' => '2.0 XEi Flex',
            'year_manufacture' => 2023,
            'year_model' => 2024,
            'color' => 'Branco Pérola',
            'fuel_type' => FuelType::Flex->value,
            'transmission' => TransmissionType::Cvt->value,
            'mileage' => 18500,
            'price' => 134900,
            'status' => VehicleStatus::Preparation->value,
            'is_featured' => false,
            'plate' => 'ABC1D23',
            'cost_price' => 115000,
            'internal_notes' => 'Entrada via troca.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('vehicles', [
        'brand' => 'Toyota',
        'model' => 'Corolla',
        'plate' => 'ABC1D23',
        'status' => VehicleStatus::Preparation->value,
    ]);
});

test('bloqueia publicar veiculo como disponivel quando possui menos de 3 fotos', function (): void {
    Storage::fake('public');

    $vehicle = Vehicle::factory()->preparation()->create([
        'plate' => 'DEF4G56',
    ]);

    // Adiciona apenas 2 fotos no banco
    $vehicle->photos()->createMany([
        ['path' => 'vehicles/foto1.jpg', 'sort_order' => 0],
        ['path' => 'vehicles/foto2.jpg', 'sort_order' => 1],
    ]);

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->fillForm([
            'status' => VehicleStatus::Available->value,
        ])
        ->call('save')
        ->assertHasFormErrors(['status']);

    expect($vehicle->fresh()->status)->toBe(VehicleStatus::Preparation);
});

test('permite publicar veiculo como disponivel quando possui 3 ou mais fotos', function (): void {
    Storage::fake('public');

    $photo1 = UploadedFile::fake()->image('foto1.jpg')->store('vehicles', 'public');
    $photo2 = UploadedFile::fake()->image('foto2.jpg')->store('vehicles', 'public');
    $photo3 = UploadedFile::fake()->image('foto3.jpg')->store('vehicles', 'public');

    $vehicle = Vehicle::factory()->preparation()->create([
        'plate' => 'GHI7J89',
    ]);

    $vehicle->photos()->createMany([
        ['path' => $photo1, 'sort_order' => 0],
        ['path' => $photo2, 'sort_order' => 1],
        ['path' => $photo3, 'sort_order' => 2],
    ]);

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->fillForm([
            'status' => VehicleStatus::Available->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($vehicle->fresh()->status)->toBe(VehicleStatus::Available);
});

test('acao rapida marcarVendido atualiza status, remove destaque e registra historico', function (): void {
    $vehicle = Vehicle::factory()->available()->create([
        'is_featured' => true,
    ]);

    Livewire::test(ListVehicles::class)
        ->callTableAction('marcarVendido', $vehicle)
        ->assertHasNoTableActionErrors();

    $vehicle->refresh();

    expect($vehicle->status)->toBe(VehicleStatus::Sold)
        ->and($vehicle->is_featured)->toBeFalse();

    $this->assertDatabaseHas('vehicle_logs', [
        'vehicle_id' => $vehicle->id,
        'user_id' => auth()->id(),
        'action' => 'status_changed',
        'old_value' => 'Disponível',
        'new_value' => 'Vendido',
    ]);
});
test('vendedor nao consegue visualizar campo de preco de custo nem excluir veiculos', function (): void {
    $seller = User::factory()->seller()->create();
    $vehicle = Vehicle::factory()->create();

    $this->actingAs($seller);

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->assertFormFieldIsHidden('cost_price')
        ->assertActionHidden('delete');
});
