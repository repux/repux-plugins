<?php

namespace Tests\Unit\Service\AmazonMWS;

use App\Entity\AmazonChannel;
use App\Entity\AmazonChannelProcess;
use App\Entity\User;
use App\Notifier\WebpushNotifier;
use App\Service\AmazonMWS\AmazonChannelProcessNotifier;
use Codeception\Stub;
use Codeception\TestCase\Test;

class AmazonChannelProcessNotifierTest extends Test
{
    const CONFIG = [
        'title' => 'some-title',
        'messages' => [
            AmazonChannelProcess::STATUS_SUCCESS => 'success',
            AmazonChannelProcess::STATUS_ERROR => 'error',
        ],
    ];

    public function testNotify()
    {
        $process = new AmazonChannelProcess();
        $store = new AmazonChannel();
        $user = new User();
        $user->setEthAddress('0x3456');
        $store->setUser($user);
        $process->setAmazonChannel($store);

        $notify = Stub\Expected::exactly(
            2,
            function (User $userToNotify, string $title, string $message) use ($user, $process) {
                $this->assertEquals($user->getEthAddress(), $userToNotify->getEthAddress());
                $this->assertEquals($title, self::CONFIG['title']);
                $this->assertEquals($message, self::CONFIG['messages'][$process->getStatus()]);
            }
        );

        /** @var WebpushNotifier $webpushNotifier */
        $webpushNotifier = Stub::makeEmpty(WebpushNotifier::class, ['notify' => $notify], $this);

        $service = new AmazonChannelProcessNotifier($webpushNotifier, self::CONFIG);

        $process->setStatus(AmazonChannelProcess::STATUS_SUCCESS);
        $service->notify($process);
        $process->setStatus(AmazonChannelProcess::STATUS_ERROR);
        $service->notify($process);
        $process->setStatus(AmazonChannelProcess::STATUS_IDLE);
        $service->notify($process);
    }
}
