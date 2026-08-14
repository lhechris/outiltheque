<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(1,true),
            'unit' => fake()->randomNumber(2,false),
            'flat_rate' => fake()->randomNumber(2,false),
            'color' => fake()->randomElement(['red','amber','cyan','green']),
            'restriction' => fake()->randomElement(['none','1 par mois','3 par mois','5 par ans'])
        ];
    }
}
