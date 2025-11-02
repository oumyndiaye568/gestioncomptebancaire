<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendClientNotification
{
    use Dispatchable, SerializesModels;

    public $client;
    public $password;
    public $smsCode;

    /**
     * Create a new event instance.
     */
    public function __construct(Client $client, string $password, string $smsCode)
    {
        $this->client = $client;
        $this->password = $password;
        $this->smsCode = $smsCode;
    }
}