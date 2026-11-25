<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $catalog = [
            ['brand' => 'Honda', 'model' => 'Civic', 'version' => '2.0 EXL FlexOne', 'base_price' => 118900],
            ['brand' => 'Toyota', 'model' => 'Corolla', 'version' => '2.0 XEi Direct Shift', 'base_price' => 132500],
            ['brand' => 'Volkswagen', 'model' => 'T-Cross', 'version' => '1.0 200 TSI Comfortline', 'base_price' => 114900],
            ['brand' => 'Jeep', 'model' => 'Compass', 'version' => '1.3 T270 Longitude', 'base_price' => 145000],
            ['brand' => 'Chevrolet', 'model' => 'Onix', 'version' => '1.0 Turbo LTZ', 'base_price' => 84900],
            ['brand' => 'Hyundai', 'model' => 'HB20', 'version' => '1.0 TGDI Platinum', 'base_price' => 88500],
            ['brand' => 'Fiat', 'model' => 'Toro', 'version' => '1.3 Turbo 270 Freedom', 'base_price' => 126900],
            ['brand' => 'Toyota', 'model' => 'Hilux', 'version' => '2.8 SRX 4x4 Diesel', 'base_price' => 249900],
            ['brand' => 'Volkswagen', 'model' => 'Nivus', 'version' => '1.0 200 TSI Highline', 'base_price' => 119800],
            ['brand' => 'BYD', 'model' => 'Dolphin', 'version' => 'GS 180EV Elétrico', 'base_price' => 139900],
        ];

        $selected = fake()->randomElement($catalog);
        $yearManufacture = fake()->numberBetween(2019, 2025);
        $yearModel = fake()->randomElement([$yearManufacture, $yearManufacture + 1]);

        $priceVariation = fake()->numberBetween(-6000, 8000);
        $price = max(45000, $selected['base_price'] + $priceVariation);
        $costPrice = round($price * fake()->randomFloat(2, 0.82, 0.90), -2);

        $allFeatures = [
            'Ar-condicionado digital',
            'Direção elétrica',
            'Central multimídia com Apple CarPlay / Android Auto',
            'Câmera de ré',
            'Sensor de estacionamento traseiro',
            'Rodas de liga leve',
            'Bancos em couro',
            'Piloto automático',
            'Controle de tração e estabilidade',
            '6 Airbags',
            'Chave presencial (Start/Stop)',
            'Faróis em LED',
        ];

        $isElectric = $selected['brand'] === 'BYD';
        $isDiesel = $selected['model'] === 'Hilux';

        $fuelType = match (true) {
            $isElectric => FuelType::Electric,
            $isDiesel => FuelType::Diesel,
            default => FuelType::Flex,
        };

        $transmission = $isElectric
            ? TransmissionType::Automatic
            : fake()->randomElement([
                TransmissionType::Automatic,
                TransmissionType::Cvt,
                TransmissionType::Manual,
            ]);

        return [
            'brand' => $selected['brand'],
            'model' => $selected['model'],
            'version' => $selected['version'],
            'year_manufacture' => $yearManufacture,
            'year_model' => $yearModel,
            'price' => $price,
            'cost_price' => $costPrice,
            'mileage' => fake()->numberBetween(8500, 92000),
            'plate' => strtoupper(fake()->bothify('???#?##')), // Padrão Mercosul (ex: ABC1D23)
            'fuel_type' => $fuelType,
            'transmission' => $transmission,
            'color' => fake()->randomElement(['Branco Pérola', 'Prata', 'Cinza Grafite', 'Preto Ninja', 'Azul Marinho', 'Vermelho']),
            'status' => fake()->randomElement([
                VehicleStatus::Available,
                VehicleStatus::Available,
                VehicleStatus::Available,
                VehicleStatus::Preparation,
                VehicleStatus::Reserved,
                VehicleStatus::Sold,
            ]),
            'is_featured' => fake()->boolean(30),
            'features' => fake()->randomElements($allFeatures, fake()->numberBetween(5, 9)),
            'description' => 'Veículo periciado com laudo cautelar aprovado, revisado em oficina própria e com garantia de procedência. Aceitamos seu usado na troca e financiamos com as melhores taxas.',
            'internal_notes' => 'Entrada via troca na loja. Revisão de óleo e filtros realizada antes de ir para o pátio.',
        ];
    }

    public function available(): static
    {
        return $this->state(fn (): array => [
            'status' => VehicleStatus::Available,
        ]);
    }

    public function preparation(): static
    {
        return $this->state(fn (): array => [
            'status' => VehicleStatus::Preparation,
            'is_featured' => false,
        ]);
    }
}
