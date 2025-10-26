<?php

namespace Database\Seeders;

use App\Models\Compte;
use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->command->info("Aucun client trouvé. Ajoutez d'abord des clients !");
            return;
        }

        Compte::factory()
            ->count(10)
            ->sequence(fn ($sequence) => ['client_id' => $clients->random()->id])
            ->create();
    }
}
