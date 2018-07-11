<?php

namespace App\Service\AmazonMWS;

use App\Entity\ChannelAmazon;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AmazonApiClientFactory
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(ChannelAmazon $channel): Client
    {
        $regionAbbr = ChannelAmazon::getMarketplaceRegionByMarketplaceId($channel->getMarketplaceId());
        $serviceName = sprintf('app_amazon_mws.client.%s', $regionAbbr);
        if (!$this->container->has($serviceName)) {
            throw new ServiceNotFoundException(sprintf('Service %s do not exist', $serviceName));
        }

        /** @var Client $amazonApiClient */
        $amazonApiClient = $this->container->get($serviceName);
        $amazonApiClient->setConfig([
            'ServiceURL' => $channel->getServiceUrl(),
        ]);

        return $amazonApiClient;
    }
}
