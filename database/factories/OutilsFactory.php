<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class OutilsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->name(),
            'description' => fake()->text(),
            'prix' => fake()->randomNumber(2),
            'nombre' => fake()->randomDigitNotNull(),
            'duree' => 7,
            'conseil' => fake()->text(),
            'precaution' => fake()->text(),
            'categorie_id' => 1,
            'file_id' => 0,
            'file2_id' => 0    
        ];
    }

}