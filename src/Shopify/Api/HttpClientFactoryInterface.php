<?php

namespace App\Shopify\Api;

use GuzzleHttp\ClientInterface;

interface HttpClientFactoryInterface
{
    public function createHttpClient(string $storeName, PublicAppCredentials $credentials): ClientInterface;
}
