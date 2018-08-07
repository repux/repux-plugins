<?php

namespace Tests\Unit\Service\Shopify;

use App\Entity\ShopifyStore;
use App\Entity\ShopifyStoreProcess;
use App\Entity\User;
use App\Notifier\WebpushNotifier;
use App\Service\Shopify\ShopifyStoreProcessNotifier;
use Codeception\Stub;
use Codeception\TestCase\Test;

class ShopifyStoreProcessNotifierTest extends Test
{
    const CONFIG = [
        'title' => 'some-title',
        'messages' => [
            ShopifyStoreProcess::STATUS_SUCCESS => 'success',
            ShopifyStoreProcess::STATUS_ERROR => 'error',
        ],
    ];

    public function testNotify()
    {
        $process = new ShopifyStoreProcess();
        $store = new ShopifyStore();
        $user = new User();
        $user->setEthAddress('0x3456');
        $store->setUser($user);
        $process->setShopifyStore($store);

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

        $service = new ShopifyStoreProcessNotifier($webpushNotifier, self::CONFIG);

        $process->setStatus(ShopifyStoreProcess::STATUS_SUCCESS);
        $service->notify($process);
        $process->setStatus(ShopifyStoreProcess::STATUS_ERROR);
        $service->notify($process);
        $process->setStatus(ShopifyStoreProcess::STATUS_IDLE);
        $service->notify($process);
    }
}
