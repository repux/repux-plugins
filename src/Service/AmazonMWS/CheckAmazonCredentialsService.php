<?php

namespace App\Service\AmazonMWS;

use App\Service\AmazonMWS\sdk\MarketplaceWebService\Client;
use App\Service\AmazonMWS\sdk\MarketplaceWebService\Model\GetReportRequestListRequest;
use App\Service\AmazonMWS\sdk\MarketplaceWebService\ClientException;
use App\Exception\CheckAmazonCredentialsException;
use App\Entity\AmazonChannel;
use App\Service\AmazonMWS\sdk\MarketplaceWebService\Model\TypeList;
use App\Service\EncryptionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckAmazonCredentialsService
{
    private $throttlingService;

    private $encryptionService;

    private $container;

    private $logger;

    private $amazonUrlService;

    public function __construct(
        AmazonThrottlingService $throttlingService,
        EncryptionService $encryptionService,
        ContainerInterface $container,
        LoggerInterface $logger,
        AmazonUrlService $amazonUrlService
    ) {
        $this->throttlingService = $throttlingService;
        $this->encryptionService = $encryptionService;
        $this->container = $container;
        $this->logger = $logger;
        $this->amazonUrlService = $amazonUrlService;
    }

    /**
     * @param AmazonChannel $channel
     *
     * @return bool
     * @throws CheckAmazonCredentialsException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
     */
    public function isAuth(AmazonChannel $channel): bool
    {
        if (!$channel->isMarketplaceSupported()) {
            throw new CheckAmazonCredentialsException("Marketplace ID is not yet supported.");
        }

        $regionAbbr = AmazonChannel::getMarketplaceRegionByMarketplaceId($channel->getMarketplaceId());

        /** @var Client $reportsClient */
        $reportsClient = $this->container->get("app_amazon_mws.client.reports.{$regionAbbr}");
        $reportsClient->setConfig(['ServiceURL' => $this->amazonUrlService->getServiceUrl($channel)]);

        $parameters = [
            'Merchant' => $channel->getMerchantId(),
            'MWSAuthToken' => $this->encryptionService->decrypt($channel->getApiToken()),
            'ReportTypeList'  => ['Type' => ['_GET_MERCHANT_LISTINGS_DATA_']],
        ];

        $request = new GetReportRequestListRequest($parameters);

        $isAuth = false;
        try {
            $reportsClient->getReportRequestList($request);

            $isAuth = true;
        } catch (ClientException $ex) {
            $this->logger->error(sprintf('[amazon] message:%s', $ex->getMessage()));
        }

        return $isAuth;
    }
}
