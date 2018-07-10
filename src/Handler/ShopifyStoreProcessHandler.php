<?php

namespace App\Handler;

use App\Entity\ShopifyStore;
use App\Entity\ShopifyStoreProcess;
use App\Service\CurrentUserService;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class ShopifyStoreProcessHandler
{
    private $entityManager;

    private $producer;

    private $currentUserService;

    public function __construct(
        EntityManager $entityManager,
        ProducerInterface $producer,
        CurrentUserService $currentUserService
    )
    {
        $this->entityManager = $entityManager;
        $this->producer = $producer;
        $this->currentUserService = $currentUserService;
    }

    public function getList(string $shopifyStoreId): array
    {
        $shopifyStore = $this->entityManager->getRepository(ShopifyStore::class)->find($shopifyStoreId);

        if ($shopifyStore instanceof ShopifyStore) {
            return $this->entityManager->getRepository(ShopifyStoreProcess::class)->findBy(
                [
                    'shopifyStore' => $shopifyStore,
                ]
            );
        }

        return [];
    }

    public function create(ShopifyStoreProcess $shopifyStoreProcess): ShopifyStoreProcess
    {
        $shopifyStoreProcess->setStatus(ShopifyStoreProcess::STATUS_IN_PROGRESS);

        $this->entityManager->persist($shopifyStoreProcess);
        $this->entityManager->flush();

        $this->producer->publish($shopifyStoreProcess->getId());

        return $shopifyStoreProcess;
    }
}
