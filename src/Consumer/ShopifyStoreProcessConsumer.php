<?php

namespace App\Consumer;

use App\Entity\ShopifyStoreProcess;
use App\Service\Shopify\ShopifyStoreProcessNotifier;
use App\Service\Shopify\ShopifyStoreProcessImportOrdersService;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class ShopifyStoreProcessConsumer implements ConsumerInterface
{
    private $entityManager;

    private $processImportOrdersService;

    private $notifier;

    public function __construct(
        EntityManager $entityManager,
        ShopifyStoreProcessImportOrdersService $processImportOrdersService,
        ShopifyStoreProcessNotifier $notifier
    ) {
        $this->entityManager = $entityManager;
        $this->processImportOrdersService = $processImportOrdersService;
        $this->notifier = $notifier;
    }

    public function execute(AMQPMessage $msg)
    {
        $processId = $msg->getBody();

        $process = $this->entityManager->getRepository(ShopifyStoreProcess::class)->find($processId);

        if ($process instanceof ShopifyStoreProcess && $process->statusIs(ShopifyStoreProcess::STATUS_IDLE)) {
            $this->processImportOrdersService->execute($process);
            $this->notifier->notify($process);
        }
    }
}
