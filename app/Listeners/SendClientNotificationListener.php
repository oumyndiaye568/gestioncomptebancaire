<?php

namespace App\Listeners;

use App\Events\CompteCreeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendClientNotificationListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CompteCreeEvent $event): void
    {
        $client = $event->client;
        $compte = $event->compte;
        $password = $event->password;
        $smsCode = $event->smsCode;

        // Envoyer l'email avec le mot de passe et les informations du compte
        try {
            Mail::raw(
                "Bonjour {$client->nom_complet},\n\n" .
                "Votre compte bancaire a été créé avec succès.\n\n" .
                "Informations de votre compte :\n" .
                "Numéro de compte : {$compte->numero_compte}\n" .
                "Type de compte : {$compte->type_compte->value}\n" .
                "Solde initial : {$compte->solde} FCFA\n\n" .
                "Informations de connexion :\n" .
                "Email : {$client->email}\n" .
                "Mot de passe temporaire : {$password}\n\n" .
                "Veuillez changer votre mot de passe lors de votre première connexion.\n\n" .
                "Cordialement,\n" .
                "L'équipe de gestion bancaire",
                function ($message) use ($client) {
                    $message->to($client->email)
                            ->subject('Création de votre compte bancaire');
                }
            );

            Log::info('Email envoyé au client après création de compte', [
                'client_id' => $client->id,
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'email' => $client->email
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email', [
                'client_id' => $client->id,
                'compte_id' => $compte->id,
                'email' => $client->email,
                'error' => $e->getMessage()
            ]);
        }

        // Envoyer le SMS avec le code d'activation via Twilio
        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $message = $twilio->messages->create(
                $client->telephone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => "Votre compte {$compte->numero_compte} a été créé. Code d'activation : {$smsCode}"
                ]
            );

            Log::info('SMS envoyé via Twilio', [
                'client_id' => $client->id,
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'telephone' => $client->telephone,
                'message_sid' => $message->sid,
                'code' => $smsCode
            ]);

        } catch (\Twilio\Exceptions\TwilioException $e) {
            Log::error('Erreur Twilio lors de l\'envoi du SMS', [
                'client_id' => $client->id,
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'telephone' => $client->telephone,
                'twilio_error_code' => $e->getCode(),
                'twilio_error_message' => $e->getMessage()
            ]);

            // Fallback: log du message qui aurait dû être envoyé
            Log::info('SMS non envoyé (Twilio error), contenu prévu', [
                'client_id' => $client->id,
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'telephone' => $client->telephone,
                'message' => "Votre compte {$compte->numero_compte} a été créé. Code d'activation : {$smsCode}"
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur générale lors de l\'envoi du SMS', [
                'client_id' => $client->id,
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'telephone' => $client->telephone,
                'error' => $e->getMessage()
            ]);
        }
    }
}