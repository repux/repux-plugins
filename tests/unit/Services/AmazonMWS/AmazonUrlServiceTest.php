<?php

namespace Tests\Unit\AmazonMWS;

use App\Entity\AmazonChannel;
use App\Service\AmazonMWS\AmazonUrlService;
use Codeception\TestCase\Test;

class AmazonUrlServiceTest extends Test
{
    public function testGetServiceUrl()
    {
        $channel = new AmazonChannel();
        $channel->setMarketplaceId(AmazonChannel::MARKETPLACE_CN);

        $service = new AmazonUrlService('');
        $this->assertEquals('https://mws.amazonservices.com.cn', $service->getServiceUrl($channel));

        $service = new AmazonUrlService('url');
        $this->assertEquals('url', $service->getServiceUrl($channel));

        $service = new AmazonUrlService(null);
        $this->assertEquals('https://mws.amazonservices.com.cn', $service->getServiceUrl($channel));
    }
}
