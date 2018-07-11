<?php

namespace App\Handler;

use App\Entity\ChannelAmazon;
use App\Service\CurrentUserService;
use App\Service\EncryptionService;
use Doctrine\ORM\EntityManager;

class ChannelAmazonHandler
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
            ->getRepository(ChannelAmazon::class)
            ->findBy(['user' => $user]);
    }

    public function create(ChannelAmazon $channel): ChannelAmazon
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
