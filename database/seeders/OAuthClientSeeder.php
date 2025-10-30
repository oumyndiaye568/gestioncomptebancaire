<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un client OAuth pour l'application admin
        $clientRepository = app(ClientRepository::class);

        $clientRepository->create(
            null, // user_id (null pour client public)
            'Gestion Compte Admin Client', // name
            'http://127.0.0.1:8000', // redirect
            null, // confidential (null = public)
            false, // personal access client
            false // password client
        )->makeVisible('secret');

        // Créer un client OAuth pour l'application client
        $clientRepository->createPasswordGrantClient(
            null, // user_id (null pour client public)
            'Gestion Compte Client Password Grant', // name
            'http://127.0.0.1:8000', // redirect
        );

        $this->command->info('Clients OAuth créés avec succès');
    }
}