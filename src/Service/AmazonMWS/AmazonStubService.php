<?php

namespace App\Service\AmazonMWS;

use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Client as MWSClient;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Model\ErrorResponse;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Model\GetReportRequestListResponse;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Model\RequestReportResponse;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrderItemsRequest;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrderItemsResponse;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrdersByNextTokenRequest;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrdersByNextTokenResponse;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrdersRequest;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrdersResponse;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceProducts\Client as MWSPClient;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Client as MWSOClient;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceProducts\Model\GetMatchingProductForIdRequest;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceProducts\Model\GetMatchingProductForIdResponse;
use App\Service\AmazonMWS\CheckAmazonCredentialsService;
use Codeception\Util\Stub;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class AmazonStubService
{
    /**
     * @var string
     */
    private $fixturesPath;

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function init(string $fixturesPath)
    {
        $this->setFixturesPath($fixturesPath);

        $amazonThrottlingService = $this->stubAmazonThrottleService();
        $this->stubCheckAmazonCredentials($amazonThrottlingService);
        $this->stubAmazonReportsClient();
        $this->stubAmazonProductsClient();
        $this->stubAmazonOrdersClient();
    }

    public function stubAmazonThrottleService()
    {
        $amazonThrottlingServiceStub = Stub::makeEmpty(AmazonThrottlingService::class, [
            'processThrottling' => function ($requestId, $requestedItemId, $restoreRate, $quota, $hourlyQuota = 0) {
                return true;
            },
        ]);
        $this->container->set('app_amazon_mws.service.amazon_throttling', $amazonThrottlingServiceStub);

        return $amazonThrottlingServiceStub;
    }

    public function stubAmazonReportsClient(&$importedProductsReport = [])
    {
        $reportsClientStub = Stub::makeEmpty(MWSClient::class, [
            'requestReport' => function ($request) {
                return $this->getRequestReportResponse();
            },
            'getReportRequestList' => function ($request) {
                return $this->getGetReportRequestListResponse();
            },
            'getReport' => function ($request) use (&$importedProductsReport) {
                $filename = "_GET_MERCHANT_LISTINGS_DATA_{$request->getReportId()}.csv";
                $requestReportResponse3 = file_get_contents(
                    $this->getFixturePath($filename)
                );

                $importedProductsReportData = str_getcsv($requestReportResponse3, "\n");
                array_shift($importedProductsReportData);
                foreach ($importedProductsReportData as $index => $line) {
                    $parsedLine = str_getcsv($line, "\t");
                    $importedProductsReport[$parsedLine[3]] = $parsedLine;
                }

                $response = new \stdClass();
                $response->fileContents = $requestReportResponse3;

                return $response;
            },
        ]);

        $this->container->set("app_amazon_mws.client.reports.NA", $reportsClientStub);
        $this->container->set("app_amazon_mws.client.reports.CN", $reportsClientStub);

        return $reportsClientStub;
    }

    public function stubCheckAmazonCredentials($amazonThrottlingServiceStub)
    {
        $amazonCheckCredentialsServiceStub = Stub::constructEmpty(
            CheckAmazonCredentialsService::class,
            [
                'throttlingService' => $amazonThrottlingServiceStub,
                'encryptionService' => $this->container->get('app_api.service.encryption_service'),
                'container' => $this->container,
            ], [
                'post' => function ($request) {
                    return true;
                },
            ]
        );

        $this->container->set('app_amazon_mws.service.check_credentials', $amazonCheckCredentialsServiceStub);

        return $amazonCheckCredentialsServiceStub;
    }

    public function stubAmazonProductsClient()
    {
        $productsClientStub = Stub::makeEmpty(
            MWSPClient::class,
            [
                'getMatchingProductForId' => function ($request) {
                    /** @var GetMatchingProductForIdRequest $request */
                    $sku = $request->getIdList()->getId()[0];

                    $filename = "GetMatchingProductForIdResponse_{$sku}.xml";
                    /** @var  $getMatchingProductForIdResponse */
                    $getMatchingProductForIdResponse = GetMatchingProductForIdResponse::fromXML(
                        file_get_contents(
                            $this->getFixturePath($filename)
                        )
                    );

                    return $getMatchingProductForIdResponse;
                },
            ]
        );

        $this->container->set("app_amazon_mws.client.products.NA", $productsClientStub);
        $this->container->set("app_amazon_mws.client.products.CN", $productsClientStub);

        return $productsClientStub;
    }

    public function stubAmazonOrdersClient()
    {
        $ordersClientStub = Stub::makeEmpty(MWSOClient::class, [
            'listOrders' => function ($request) {
                /** @var ListOrdersRequest $request */
                $filename = "ListOrdersRequestResponse.xml";
                $listOrdersRequestResponse = ListOrdersResponse::fromXML(
                    file_get_contents(
                        $this->getFixturePath($filename)
                    )
                );

                return $listOrdersRequestResponse;
            },
            'listOrdersByNextToken' => function ($request) {
                /** @var ListOrdersByNextTokenRequest $request */
                $nextToken = $request->getNextToken();
                $filename = "ListOrdersByNextTokenRequestResponse_{$nextToken}.xml";
                $listOrdersByNextTokenRequestResponse = ListOrdersByNextTokenResponse::fromXML(
                    file_get_contents(
                        $this->getFixturePath($filename)
                    )
                );

                return $listOrdersByNextTokenRequestResponse;
            },
            'listOrderItems' => function ($request) {
                /** @var ListOrderItemsRequest $request */
                $amazonOrderId = $request->getAmazonOrderId();
                $filename = "ListOrderItemsRequest_{$amazonOrderId}.xml";
                $listOrderItemsResponse = ListOrderItemsResponse::fromXML(
                    file_get_contents(
                        $this->getFixturePath($filename)
                    )
                );

                return $listOrderItemsResponse;
            },
        ]);

        $this->container->set("app_amazon_mws.client.orders.NA", $ordersClientStub);
        $this->container->set("app_amazon_mws.client.orders.CN", $ordersClientStub);

        return $ordersClientStub;
    }

    protected function getRequestReportResponse(
        string $fixtureFilename = 'RequestReportResponse.xml'
    ): RequestReportResponse {
        return RequestReportResponse::fromXML(
            file_get_contents(
                $this->getFixturePath($fixtureFilename)
            )
        );
    }

    protected function getErrorRequestReportResponse(
        string $fixtureFilename = 'RequestReportResponse.xml'
    ): ErrorResponse {
        return ErrorResponse::fromXML(
            file_get_contents(
                $this->getFixturePath($fixtureFilename)
            )
        );
    }

    protected function getGetReportRequestListResponse(
        string $fixtureFilename = 'GetReportRequestListResponse_2.xml'
    ): GetReportRequestListResponse {
        return GetReportRequestListResponse::fromXML(
            file_get_contents(
                $this->getFixturePath($fixtureFilename)
            )
        );
    }

    private function getFixturePath(string $filename = ''): string
    {
        return rtrim(sprintf('%s/%s', $this->fixturesPath, $filename), '/');
    }

    public function setFixturesPath(string $fixturesPath)
    {
        if (!is_dir($fixturesPath)) {
            throw new FileNotFoundException($fixturesPath);
        }

        $this->fixturesPath = rtrim($fixturesPath, '/');
    }
}
