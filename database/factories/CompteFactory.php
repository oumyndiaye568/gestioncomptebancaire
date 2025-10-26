<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{

    protected $model = \App\Models\Compte::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'titulaire' => $this->faker->name(),
             'numero_compte' => 'COMP-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
            'type_compte' => fake()->randomElement(['cheque', 'epargne']),
            'etat_compte' => fake()->randomElement(['actif', 'inactif', 'bloque']),
            'solde' => fake()->numberBetween(1000, 500000),
            'motif_blocage' => null,
            'client_id' => Client::factory(),
        ];
    }
}
