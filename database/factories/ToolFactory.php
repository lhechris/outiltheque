<?php

namespace Database\Factories;

use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(5),
            'description' => fake()->sentence(),
            'advice'=> fake()->sentence(),
            'caution'=> fake()->sentence(),
            'image' => 'uploads/'.fake()->word(10).'.jpg',
            'icon' => 'uploads/'.fake()->word(10).'.jpg',
            'active' => 1,
            'number' => fake()->randomDigit(),
        ];
    }
}
