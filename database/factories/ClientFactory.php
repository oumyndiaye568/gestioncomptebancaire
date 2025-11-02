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
           'telephone' => '+221' . $this->faker->numberBetween(700000000, 799999999),
           'adresse' => $this->faker->address(),
           'nci' => $this->generateValidNci(),
           'password' => bcrypt('password123'),
           'code_verification' => str_pad($this->faker->numberBetween(0, 999999), 6, '0', STR_PAD_LEFT),
           'created_at' => now(),
           'updated_at' => now(),
       ];
   }

   /**
    * Générer un NCI valide selon le format sénégalais
    */
   private function generateValidNci(): string
   {
       // Générer une année entre 1950 et 2005
       $year = $this->faker->numberBetween(50, 99);
       $month = str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT);
       $day = str_pad($this->faker->numberBetween(1, 28), 2, '0', STR_PAD_LEFT); // Pour éviter les problèmes de jours dans le mois

       // Numéro de série (7 chiffres)
       $serial = str_pad($this->faker->numberBetween(0, 9999999), 7, '0', STR_PAD_LEFT);

       return $year . $month . $day . $serial;
   }
}
