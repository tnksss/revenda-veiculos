<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('vehicles');

        $vehicles = Vehicle::factory()
            ->count(10)
            ->available()
            ->create();

        $reservedVehicles = Vehicle::factory()
            ->count(2)
            ->reserved()
            ->create();

        $preparationVehicles = Vehicle::factory()
            ->count(3)
            ->preparation()
            ->create();

        foreach ($vehicles->concat($reservedVehicles) as $vehicle) {
            $this->attachSamplePhotos($vehicle, 3);
        }

        // Veículos em preparação ficam com apenas 1 foto para testar a trava de publicação
        foreach ($preparationVehicles as $vehicle) {
            $this->attachSamplePhotos($vehicle, 1);
        }
    }

    private function attachSamplePhotos(Vehicle $vehicle, int $count): void
    {
        $angles = ['Frente / Lateral', 'Interior / Bancos', 'Painel / Multimídia'];
        $palettes = [
            [30, 41, 59],   // Slate 800
            [15, 23, 42],   // Slate 900
            [51, 65, 85],   // Slate 700
        ];

        for ($i = 0; $i < $count; $i++) {
            $filename = 'vehicles/'.Str::uuid().'.jpg';
            $label = "{$vehicle->brand} {$vehicle->model} ({$vehicle->year_model}) - ".($angles[$i] ?? 'Foto '.($i + 1));
            $rgb = $palettes[$i % count($palettes)];

            $this->generatePlaceholderImage($filename, $label, $rgb);

            $vehicle->photos()->create([
                'path' => $filename,
                'is_cover' => $i === 0,
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $bgRgb
     */
    private function generatePlaceholderImage(string $storagePath, string $text, array $bgRgb): void
    {
        $width = 1280;
        $height = 800;
        $image = imagecreatetruecolor($width, $height);

        if ($image === false) {
            return;
        }

        $bgColor = imagecolorallocate($image, $bgRgb[0], $bgRgb[1], $bgRgb[2]);
        $accentColor = imagecolorallocate($image, 245, 158, 11); // Amber 500
        $textColor = imagecolorallocate($image, 248, 250, 252); // Slate 50

        if ($bgColor !== false) {
            imagefill($image, 0, 0, $bgColor);
        }

        if ($accentColor !== false) {
            imagefilledrectangle($image, 0, $height - 16, $width, $height, $accentColor);
        }

        if ($textColor !== false) {
            imagestring($image, 5, 60, (int) ($height / 2) - 20, 'AUTOPRIME SEMINOVOS', $accentColor ?: $textColor);
            imagestring($image, 5, 60, (int) ($height / 2) + 15, mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'), $textColor);
        }

        ob_start();
        imagejpeg($image, null, 85);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($storagePath, $binary);
    }
}
