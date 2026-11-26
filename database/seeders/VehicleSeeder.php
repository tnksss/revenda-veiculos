<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::factory()
            ->count(8)
            ->available()
            ->create();

        $vehicles->take(3)->each(fn (Vehicle $vehicle) => $vehicle->update(['is_featured' => true]));

        Vehicle::factory()->count(2)->create(['status' => VehicleStatus::Reserved]);
        Vehicle::factory()->count(2)->create(['status' => VehicleStatus::Preparation]);
        Vehicle::factory()->count(2)->sold()->create();

        Vehicle::all()->each(function (Vehicle $vehicle): void {
            for ($i = 1; $i <= 4; $i++) {
                $tempPath = $this->createFakeCarPhoto(
                    "{$vehicle->brand} {$vehicle->model} ({$vehicle->year_model}) - Foto {$i}",
                    $i
                );

                $vehicle->addMedia($tempPath)
                    ->usingFileName("{$vehicle->slug}-{$i}.jpg")
                    ->toMediaCollection('gallery');
            }
        });
    }

    private function createFakeCarPhoto(string $label, int $index): string
    {
        $width = 1280;
        $height = 800;
        $img = imagecreatetruecolor($width, $height);

        $palettes = [
            [15, 23, 42],
            [30, 41, 59],
            [24, 24, 27],
            [17, 24, 39],
        ];

        [$r, $g, $b] = $palettes[($index - 1) % count($palettes)];
        $bg = imagecolorallocate($img, $r, $g, $b);
        imagefill($img, 0, 0, $bg);

        $accent = imagecolorallocate($img, 245, 158, 11);
        $textColor = imagecolorallocate($img, 248, 250, 252);

        imagefilledrectangle($img, 80, 340, 1200, 346, $accent);
        imagestring($img, 5, 80, 300, strtoupper($label), $textColor);
        imagestring($img, 4, 80, 370, 'AUTO PRIME SEMINOVOS - ESTOQUE DEMONSTRACAO', $accent);

        $tempPath = tempnam(sys_get_temp_dir(), 'car_').'.jpg';
        imagejpeg($img, $tempPath, 88);
        imagedestroy($img);

        return $tempPath;
    }
}
