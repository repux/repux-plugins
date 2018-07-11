<?php

namespace App\Handler;

use App\Entity\ChannelAmazon;
use App\Entity\ChannelAmazonProcess;
use App\Service\CurrentUserService;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class ChannelAmazonProcessHandler
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
            ->getRepository(ChannelAmazon::class)
            ->find($channelAmazonId);

        if ($channelAmazon instanceof ChannelAmazon) {
            return $this->entityManager
                ->getRepository(ChannelAmazonProcess::class)
                ->findBy(['channelAmazon' => $channelAmazon]);
        }
    }

    public function create(ChannelAmazonProcess $channelAmazonProcess): ChannelAmazonProcess
    {
        $channelAmazonProcess->setStatus(ChannelAmazonProcess::STATUS_IN_PROGRESS);

        $this->entityManager->persist($channelAmazonProcess);
        $this->entityManager->flush();

        $this->producer->publish($channelAmazonProcess->getId());

        return $channelAmazonProcess;
    }
}
