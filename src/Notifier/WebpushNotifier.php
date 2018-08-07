<?php

namespace App\Notifier;

use App\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class WebpushNotifier
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function notify(User $user, string $title, string $message)
    {
        $this->client->post('', [
            RequestOptions::VERIFY => false,
            RequestOptions::JSON => [
                'recipientAddress' => $user->getEthAddress(),
                'params' => [
                    'title' => $title,
                    'message' => $message,
                ],
            ],
        ]);
    }
}
