<?php

declare(strict_types=1);

use App\Actions\Vehicles\MarkVehicleAsSoldAction;
use App\Actions\Vehicles\PublishVehicleAction;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('MarkVehicleAsSoldAction atualiza status para vendido e remove destaque', function () {
    $vehicle = Vehicle::factory()->available()->create(['is_featured' => true]);

    $updated = app(MarkVehicleAsSoldAction::class)->execute($vehicle);

    expect($updated->status)->toBe(VehicleStatus::Sold)
        ->and($updated->is_featured)->toBeFalse();
});

test('PublishVehicleAction publica veiculo quando possui 3 ou mais fotos', function () {
    Storage::fake('public');

    $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Preparation]);

    for ($i = 1; $i <= 3; $i++) {
        $vehicle->addMedia(UploadedFile::fake()->image("photo-{$i}.jpg", 800, 600))
            ->toMediaCollection('gallery');
    }

    $updated = app(PublishVehicleAction::class)->execute($vehicle);

    expect($updated->status)->toBe(VehicleStatus::Available);
});

test('PublishVehicleAction lanca excecao quando veiculo possui menos de 3 fotos', function () {
    Storage::fake('public');

    $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Preparation]);

    for ($i = 1; $i <= 2; $i++) {
        $vehicle->addMedia(UploadedFile::fake()->image("photo-{$i}.jpg", 800, 600))
            ->toMediaCollection('gallery');
    }

    expect(fn () => app(PublishVehicleAction::class)->execute($vehicle))
        ->toThrow(ValidationException::class);
});
