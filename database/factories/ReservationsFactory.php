<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ReservationsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'outil_id' => 1,
            'nom' => fake()->name(),
            'telephone' => fake()->phoneNumber(),
            'email' => fake()->email(),        
            'debut' => fake()->date('Y-m-d'),
            'fin' => fake()->date('Y-m-d'),
            'state' => 'Réservé',
            'paiement_state' => 'Non payé',
            'paiement_id' => fake()->randomNumber(6,true),
            'commentaire' => fake()->text()    
        ];
    }

}