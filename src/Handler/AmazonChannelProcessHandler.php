<?php

namespace App\Handler;

use App\Entity\AmazonChannel;
use App\Entity\AmazonChannelProcess;
use App\Service\CurrentUserService;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class AmazonChannelProcessHandler
{
    private $entityManager;

    private $producer;

    private $currentUserService;

    public function __construct(
        EntityManager $entityManager,
        ProducerInterface $producer,
        CurrentUserService $currentUserService
    ) {
        $this->entityManager = $entityManager;
        $this->producer = $producer;
        $this->currentUserService = $currentUserService;
    }

    public function getList(string $channelAmazonId)
    {
        $channelAmazon = $this->entityManager
            ->getRepository(AmazonChannel::class)
            ->find($channelAmazonId);

        if ($channelAmazon instanceof AmazonChannel) {
            return $this->entityManager
                ->getRepository(AmazonChannelProcess::class)
                ->findBy(['amazonChannel' => $channelAmazon]);
        }
    }

    public function create(AmazonChannelProcess $channelAmazonProcess): AmazonChannelProcess
    {
        $channelAmazonProcess->setStatus(AmazonChannelProcess::STATUS_IN_PROGRESS);

        $this->entityManager->persist($channelAmazonProcess);
        $this->entityManager->flush();

        $this->producer->publish($channelAmazonProcess->getId());

        return $channelAmazonProcess;
    }
}
