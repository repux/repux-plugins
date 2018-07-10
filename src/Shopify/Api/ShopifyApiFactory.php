<?php

namespace App\Shopify\Api;

use App\Entity\ShopifyStore;

class ShopifyApiFactory
{
    private $httpClientFactory;

    public function __construct(HttpClientFactoryInterface $httpClientFactory)
    {
        $this->httpClientFactory = $httpClientFactory;
    }

    public function getForStore(ShopifyStore $shopifyStore): ShopifyApi
    {
        $client = $this->httpClientFactory->createHttpClient(
            $shopifyStore->getName(),
            new PublicAppCredentials($shopifyStore->getAccessToken())
        );

        return new ShopifyApi($client);
    }
}
