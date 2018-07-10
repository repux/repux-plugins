<?php

namespace App\Shopify\Api;

use App\Shopify\Api\Endpoint\AbstractEndpoint;
use App\Shopify\Api\Endpoint\Order;
use GuzzleHttp\ClientInterface;

/**
 * @property Order Order
 */
class ShopifyApi
{
    private $client;

    /**
     * @var string[]
     */
    private $endpointClasses = [
        'Order' => Order::class,
    ];

    /**
     * @var AbstractEndpoint[]
     */
    private $endpoints;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getEndpoint(string $endpoint): AbstractEndpoint
    {
        if (isset($this->endpoints[$endpoint])) {
            return $this->endpoints[$endpoint];
        }

        if (!isset($this->endpointClasses[$endpoint])) {
            throw new \InvalidArgumentException(sprintf('Endpoint %s does not exist', $endpoint));
        }

        $class = $this->endpointClasses[$endpoint];

        return $this->endpoints[$endpoint] = new $class($this->client);
    }

    public function __get($name)
    {
        return $this->getEndpoint($name);
    }
}
