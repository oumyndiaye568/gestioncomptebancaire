<?php

namespace App\Events;

use App\Models\Client;
use App\Models\Compte;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompteCreeEvent
{
    use Dispatchable, SerializesModels;

    public $client;
    public $compte;
    public $password;
    public $smsCode;

    /**
     * Create a new event instance.
     */
    public function __construct(Client $client, Compte $compte, string $password, string $smsCode)
    {
        $this->client = $client;
        $this->compte = $compte;
        $this->password = $password;
        $this->smsCode = $smsCode;
    }
}