<?php

namespace App\Service\AmazonMWS;

use App\Entity\AmazonChannel;
use App\Service\AmazonMWS\sdk\MarketplaceWebService\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AmazonApiClientFactory
{
    private $container;

    private $amazonUrlService;

    public function __construct(ContainerInterface $container, AmazonUrlService $amazonUrlService)
    {
        $this->container = $container;
        $this->amazonUrlService = $amazonUrlService;
    }

    public function create(AmazonChannel $channel): Client
    {
        $regionAbbr = AmazonChannel::getMarketplaceRegionByMarketplaceId($channel->getMarketplaceId());
        $serviceName = sprintf('app_amazon_mws.client.%s', $regionAbbr);
        if (!$this->container->has($serviceName)) {
            throw new ServiceNotFoundException(sprintf('Service %s do not exist', $serviceName));
        }

        /** @var Client $amazonApiClient */
        $amazonApiClient = $this->container->get($serviceName);
        $amazonApiClient->setConfig([
            'ServiceURL' => $this->amazonUrlService->getServiceUrl($channel),
        ]);

        return $amazonApiClient;
    }
}
