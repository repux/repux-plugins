<?php

namespace App\Service\Shopify;

use App\Entity\ShopifyStoreProcess;
use App\Notifier\WebpushNotifier;

class ShopifyStoreProcessNotifier
{
    private $webpushNotifier;

    private $config;

    public function __construct(WebpushNotifier $webpushNotifier, array $config)
    {
        $this->webpushNotifier = $webpushNotifier;
        $this->config = $config;
    }

    public function notify(ShopifyStoreProcess $process)
    {
        $message = $this->getMessageFor($process);

        if (!$message) {
            return;
        }

        $this->webpushNotifier->notify($process->getShopifyStore()->getUser(), $this->config['title'], $message);
    }

    private function getMessageFor(ShopifyStoreProcess $process): ?string
    {
        return $this->config['messages'][$process->getStatus()] ?? null;
    }
}
