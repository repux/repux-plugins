<?php

namespace App\Service\AmazonMWS;

use App\Entity\AmazonChannelProcess;
use App\Notifier\WebpushNotifier;

class AmazonChannelProcessNotifier
{
    private $webpushNotifier;

    private $config;

    public function __construct(WebpushNotifier $webpushNotifier, array $config)
    {
        $this->webpushNotifier = $webpushNotifier;
        $this->config = $config;
    }

    public function notify(AmazonChannelProcess $process)
    {
        $message = $this->getMessageFor($process);

        if (!$message) {
            return;
        }

        $this->webpushNotifier->notify($process->getAmazonChannel()->getUser(), $this->config['title'], $message);
    }

    private function getMessageFor(AmazonChannelProcess $process): ?string
    {
        return $this->config['messages'][$process->getStatus()] ?? null;
    }
}
