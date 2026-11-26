<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory()->available(),
            'assigned_to' => null,
            'name' => fake()->name(),
            'phone' => '(42) 999'.fake()->numerify('##-####'),
            'email' => fake()->safeEmail(),
            'has_trade_in' => fake()->boolean(30),
            'wants_financing' => fake()->boolean(45),
            'message' => 'Gostaria de mais informações sobre condições de pagamento e avaliação.',
            'status' => LeadStatus::New,
            'internal_notes' => null,
        ];
    }
}
