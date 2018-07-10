<?php

namespace App\Shopify\Consumer;

use App\Entity\ShopifyStoreProcess;
use App\Service\ShopifyStoreProcessImportOrdersService;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class ShopifyStoreProcessConsumer implements ConsumerInterface
{
    private $entityManager;

    private $processImportOrdersService;

    public function __construct(
        EntityManager $entityManager,
        ShopifyStoreProcessImportOrdersService $processImportOrdersService
    ) {
        $this->entityManager = $entityManager;
        $this->processImportOrdersService = $processImportOrdersService;
    }

    public function execute(AMQPMessage $msg)
    {
        $processId = $msg->getBody();

        $process = $this->entityManager->getRepository(ShopifyStoreProcess::class)->find($processId);

        if ($process instanceof ShopifyStoreProcess) {
            $this->processImportOrdersService->execute($process);
        }
    }
}
