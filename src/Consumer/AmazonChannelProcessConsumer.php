<?php

namespace App\Consumer;

use App\Entity\AmazonChannelProcess;
use App\Service\AmazonMWS\AmazonChannelProcessImportOrdersService;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AmazonChannelProcessConsumer implements ConsumerInterface
{
    private $entityManager;

    private $processImportOrdersService;

    public function __construct(
        EntityManager $entityManager,
        AmazonChannelProcessImportOrdersService $processImportOrdersService
    )
    {
        $this->entityManager = $entityManager;
        $this->processImportOrdersService = $processImportOrdersService;
    }

    public function execute(AMQPMessage $msg)
    {
        $processId = $msg->getBody();

        $process = $this->entityManager
            ->getRepository(AmazonChannelProcess::class)
            ->find($processId);

        if ($process instanceof AmazonChannelProcess) {
            // Now we only support orders import
            if ($process->getType() === AmazonChannelProcess::TYPE_IMPORT_ORDERS) {
                $this->processImportOrdersService->execute($process);
            }
        }
    }
}
