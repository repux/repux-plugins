<?php

namespace App\Service\AmazonMWS;

use App\Entity\AmazonChannel;

class AmazonUrlService
{
    private $fixedUrl;

    public function __construct(?string $fixedUrl)
    {
        $this->fixedUrl = $fixedUrl ? $fixedUrl : null;
    }

    public function getServiceUrl(AmazonChannel $amazonChannel)
    {
        return $this->fixedUrl ?? AmazonChannel::getServiceUrlByMarketplaceId($amazonChannel->getMarketplaceId());
    }
}
