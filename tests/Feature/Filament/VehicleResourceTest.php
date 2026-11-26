<?php

declare(strict_types=1);

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\UserRole;
use App\Enums\VehicleStatus;
use App\Filament\Resources\VehicleResource\Pages\CreateVehicle;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('allows uploading multiple gallery photos at once and generates conversions', function (): void {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($admin);

    $photos = [
        UploadedFile::fake()->image('front.jpg', 1200, 800),
        UploadedFile::fake()->image('side.jpg', 1200, 800),
        UploadedFile::fake()->image('interior.jpg', 1200, 800),
    ];

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'brand' => 'Honda',
            'model' => 'Civic',
            'version' => '2.0 EXL Automático',
            'year_manufacture' => 2022,
            'year_model' => 2022,
            'color' => 'Preto',
            'fuel_type' => FuelType::Flex->value,
            'transmission' => TransmissionType::Automatic->value,
            'mileage' => 32000,
            'price' => 128900.00,
            'status' => VehicleStatus::Available->value,
            'plate' => 'ABC1D23',
            'gallery' => $photos,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $vehicle = Vehicle::where('model', 'Civic')->firstOrFail();

    expect($vehicle->getMedia('gallery'))->toHaveCount(3);

    $firstMedia = $vehicle->getFirstMedia('gallery');
    expect($firstMedia?->hasGeneratedConversion('thumb'))->toBeTrue()
        ->and($firstMedia?->hasGeneratedConversion('large'))->toBeTrue();
});

it('blocks publishing vehicle as available with fewer than 3 photos in gallery', function (): void {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($admin);

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'version' => '2.0 XEi',
            'year_manufacture' => 2021,
            'year_model' => 2021,
            'color' => 'Prata',
            'fuel_type' => FuelType::Flex->value,
            'transmission' => TransmissionType::Automatic->value,
            'mileage' => 45000,
            'price' => 119000.00,
            'status' => VehicleStatus::Available->value,
            'plate' => 'XYZ9K88',
            'gallery' => [
                UploadedFile::fake()->image('only-one.jpg', 800, 600),
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['status']);
});
