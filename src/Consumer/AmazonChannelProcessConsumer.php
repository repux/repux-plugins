<?php

namespace App\Consumer;

use App\Entity\AmazonChannel;
use App\Entity\AmazonChannelProcess;
use App\Service\AmazonMWS\AmazonChannelProcessImportOrdersService;
use App\Service\AmazonMWS\AmazonChannelProcessNotifier;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AmazonChannelProcessConsumer implements ConsumerInterface
{
    private $entityManager;

    private $processImportOrdersService;

    private $notifier;

    public function __construct(
        EntityManager $entityManager,
        AmazonChannelProcessImportOrdersService $processImportOrdersService,
        AmazonChannelProcessNotifier $notifier
    ) {
        $this->entityManager = $entityManager;
        $this->processImportOrdersService = $processImportOrdersService;
        $this->notifier = $notifier;
    }

    public function execute(AMQPMessage $msg)
    {
        $processId = $msg->getBody();

        $process = $this->entityManager
            ->getRepository(AmazonChannelProcess::class)
            ->find($processId);

        if (!$process instanceof AmazonChannelProcess
            || $process->getType() !== AmazonChannelProcess::TYPE_IMPORT_ORDERS
            || !$process->getAmazonChannel()->statusIs(AmazonChannel::STATUS_IDLE)
        ) {
            return false;
        }

        $this->processImportOrdersService->execute($process);
        $this->notifier->notify($process);
    }
}
