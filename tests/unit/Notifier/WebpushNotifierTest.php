<?php

namespace Tests\Unit\Service;

use App\Entity\User;
use App\Notifier\WebpushNotifier;
use Codeception\Stub;
use Codeception\TestCase\Test;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class WebpushNotifierTest extends Test
{
    public function testNotify()
    {
        $title = 'title';
        $message = 'message';
        $user = new User();
        $user->setEthAddress('0x12345');

        $post = Stub\Expected::once(function ($url, $options) use ($user, $title, $message) {
            $this->assertEquals('', $url);
            $this->assertArraySubset(
                [
                    RequestOptions::VERIFY => false,
                    RequestOptions::JSON => [
                        'recipientAddress' => $user->getEthAddress(),
                        'params' => [
                            'title' => $title,
                            'message' => $message,
                        ],
                    ],
                ],
                $options
            );
        });

        $client = Stub::makeEmpty(Client::class, ['post' => $post], $this);

        $service = new WebpushNotifier($client);

        $service->notify($user, $title, $message);
    }
}
