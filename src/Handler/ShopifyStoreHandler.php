<?php

namespace App\Handler;

use App\Entity\ShopifyStore;
use App\Repository\ShopifyStoreRepository;
use App\Service\CurrentUserService;
use App\Shopify\Api\ShopifyApiFactory;
use App\Shopify\Authentication\OAuth as ShopifyAuth;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ShopifyStoreHandler
{
    protected $entityManager;

    protected $currentUserService;

    /** @var ShopifyStoreRepository  */
    protected $repository;

    private $shopifyAuth;

    private $shopifyApiFactory;

    public function __construct(
        EntityManager $entityManager,
        CurrentUserService $currentUserService,
        ShopifyAuth $shopifyAuth,
        ShopifyApiFactory $shopifyApiFactory
    )
    {
        $this->entityManager = $entityManager;
        $this->currentUserService = $currentUserService;
        $this->repository = $entityManager->getRepository(ShopifyStore::class);
        $this->shopifyAuth = $shopifyAuth;
        $this->shopifyApiFactory = $shopifyApiFactory;
    }

    public function getList()
    {
        return $this->repository->findAllByUser($this->currentUserService->getUser());
    }

    public function findOne(string $id)
    {
        return $this->repository->findOne($this->currentUserService->getUser(), $id);
    }

    /**
     * @return ShopifyStore|null
     */
    public function getCurrentUserShopifyStore()
    {
        if (!empty($this->currentUserService->getUser()->getShopifyStore())) {
            return $this->currentUserService->getUser()->getShopifyStore();
        }

        return null;
    }

    public function isAlreadyIntegrated(): bool
    {
        if (!empty($this->currentUserService->getUser())) {
            $shopifyStore = $this->currentUserService->getUser()->getShopifyStore();

            return !empty($shopifyStore) && !empty($shopifyStore->getAccessToken());
        }

        return false;
    }

    public function create(ShopifyStore $shopifyStore): array
    {
        $shopifyStore->setUser($this->currentUserService->getUser());

        $authorizeUrl = $this->shopifyAuth->auth($shopifyStore);

        $this->entityManager->persist($shopifyStore);
        $this->entityManager->flush($shopifyStore);

        return [$shopifyStore, $authorizeUrl];
    }

    public function verify(Request $request): ShopifyStore
    {
        $authCode = $request->get('code');
        $storeName = $request->get('shop');
        $nonce = $request->get('state');
        $hmac = $request->get('hmac');

        if (empty($authCode) || empty($storeName) || empty($nonce) || empty($hmac)) {
            throw new BadRequestHttpException(
                'Request is missing required parameters: "code", "shop", "state", "hmac".'
            );
        }

        if ($domainPos = strpos($storeName, '.myshopify.com') !== false) {
            $storeName = str_replace('.myshopify.com', '', $storeName);
        } else {
            throw new BadRequestHttpException(
                'Request has invalid required parameter: "shop".'
            );
        }

        /** @var ShopifyStore $shopifyStore */
        $shopifyStore = $this->repository->findOneBy(
            [
                'name' => $storeName,
                'nonce' => $nonce,
            ]
        );

        if (empty($shopifyStore)) {
            throw new EntityNotFoundException();
        }

        $shopifyStore = $this->shopifyAuth->verify($shopifyStore, $request);

        $this->entityManager->persist($shopifyStore);
        $this->entityManager->flush($shopifyStore);

        return $shopifyStore;
    }
}
