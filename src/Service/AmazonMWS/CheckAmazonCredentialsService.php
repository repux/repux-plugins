<?php

namespace App\Service\AmazonMWS;

use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Client;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Model\GetReportRequestListRequest;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\ClientException;
use App\Exception\CheckAmazonCredentialsException;
use App\Entity\ChannelAmazon;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Model\TypeList;
use App\Service\EncryptionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckAmazonCredentialsService
{
    private $throttlingService;

    private $encryptionService;

    private $container;

    private $logger;

    private $supportMarketplaseIds = [
        'AAHKV2X7AFYLW', //CN
        'A2EUQ1WTGCTBG2', //CA
        'ATVPDKIKX0DER', //US
        'A1AM78C64UM0Y8', //MX
    ];

    public function __construct(
        AmazonThrottlingService $throttlingService,
        EncryptionService $encryptionService,
        ContainerInterface $container,
        LoggerInterface $logger
    ) {
        $this->throttlingService = $throttlingService;
        $this->encryptionService = $encryptionService;
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @param ChannelAmazon $channel
     *
     * @return bool
     * @throws CheckAmazonCredentialsException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
     */
    public function isAuth(ChannelAmazon $channel): bool
    {
        if (!in_array($channel->getMarketplaceId(), $this->supportMarketplaseIds)) {
            throw new CheckAmazonCredentialsException("Marketplace ID is not yet supported.");
        }

        $regionAbbr = ChannelAmazon::getMarketplaceRegionByMarketplaceId($channel->getMarketplaceId());

        /** @var Client $reportsClient */
        $reportsClient = $this->container->get("app_amazon_mws.client.reports.{$regionAbbr}");
        $reportsClient->setConfig(['ServiceURL' => $channel->getServiceUrl()]);

        $parameters = [
            'Merchant' => $channel->getMerchantId(),
            'MWSAuthToken' => $this->encryptionService->decrypt($channel->getApiToken()),
            'ReportTypeList' => new TypeList(['Type' => ['_GET_MERCHANT_LISTINGS_DATA_']]),
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
