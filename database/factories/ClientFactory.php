<?php

namespace Database\Factories;

use Illuminate\Support\Str; 
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{

     protected $model = Client::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
        'nom_complet' => $this->faker->name(),
        'email' => $this->faker->unique()->safeEmail(),
        'telephone' => $this->faker->phoneNumber(),
        'adresse' => $this->faker->address(),
        'created_at' => now(),
        'updated_at' => now(),
        ];
    }
}
