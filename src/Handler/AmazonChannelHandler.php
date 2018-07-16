<?php

namespace App\Handler;

use App\Entity\AmazonChannel;
use App\Service\CurrentUserService;
use App\Service\EncryptionService;
use Doctrine\ORM\EntityManager;

class AmazonChannelHandler
{
    private $currentUserService;

    private $entityManager;

    private $encryptionService;

    public function __construct(
        CurrentUserService $currentUserService,
        EntityManager $entityManager,
        EncryptionService $encryptionService
    ) {
        $this->currentUserService = $currentUserService;
        $this->entityManager = $entityManager;
        $this->encryptionService = $encryptionService;
    }

    public function getList()
    {
        $user = $this->currentUserService->getUser();

        return $this->entityManager
            ->getRepository(AmazonChannel::class)
            ->findBy(['user' => $user]);
    }

    public function create(AmazonChannel $channel): AmazonChannel
    {
        $user = $this->currentUserService->getUser();
        $channel->setUser($user);
        $channel->setApiToken($this->encryptionService
            ->encrypt($channel->getApiToken())
        );

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }
}
