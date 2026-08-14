<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'reference' => fake()->numerify('REF######'),
        'name' => fake()->name(),
        'email' =>fake()->email(),
        'phone' =>fake()->numerify('##########'),       
        'date_start' => \Carbon\Carbon::now()->format('Y-m-d h:i:s'),
        'date_end' => \Carbon\Carbon::now()->addDays(6)->format('Y-m-d h:i:s'),
        'state' => \App\Models\Reservation::STATE_RESERVED,
        'payment_state' => \App\Models\Reservation::PAYMENT_STATE_UNPAID,
        'comment' => fake()->sentence()
        ];
    }
}
