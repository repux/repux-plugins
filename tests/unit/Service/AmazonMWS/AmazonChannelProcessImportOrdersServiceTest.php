<?php

namespace Tests\Unit\Service\AmazonMWS;

use App\Entity\AmazonChannel;
use App\Entity\AmazonChannelProcess;
use App\Handler\DataFileHandler;
use App\Service\AmazonMWS\AmazonChannelProcessImportOrdersService;
use App\Service\AmazonMWS\AmazonThrottlingService;
use App\Service\AmazonMWS\AmazonUrlService;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Client;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Model\ListOrdersRequest;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Model\ListOrdersResponse;
use App\Service\ArrayToCsvService;
use App\Service\EncryptionService;
use Codeception\Stub;
use Codeception\TestCase\Test;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;

class AmazonChannelProcessImportOrdersServiceTest extends Test
{
    const PROCESS_PARAMETERS = '{"created_at_from": "2016-01-01 00:00:00", "created_at_to": "2018-01-01 00:01:11"}';

    public function testProcessParametersImport()
    {
        $listOrders = Stub\Expected::once(function (ListOrdersRequest $request) {
            $this->assertEquals('2016-01-01T00:00:00+00:00', $request->getCreatedAfter());
            $this->assertEquals('2018-01-01T00:01:11+00:00', $request->getCreatedBefore());

            return Stub::makeEmpty(ListOrdersResponse::class);
        });
        $container = $this->getContainerStub([
            'app_amazon_mws.client.orders.CN' => $this->stubAmazonClient(['listOrders' => $listOrders])
        ]);

        $service = new AmazonChannelProcessImportOrdersService(
            $this->getEntityManagerStub(),
            $this->getAmazonThrottlingService(),
            $this->getEncryptionServiceStub(),
            $this->getLoggerStub(),
            $container,
            new ArrayToCsvService(),
            $this->getDataFileHandlerStub(),
            new AmazonUrlService('url')
        );

        $channel = new AmazonChannel();
        $channel->setId(1);
        $channel->setMarketplaceId(AmazonChannel::MARKETPLACE_CN);
        $channel->setApiToken('api-token');

        $process = new AmazonChannelProcess();
        $process->setAmazonChannel($channel);
        $process->setParameters(self::PROCESS_PARAMETERS);

        $service->execute($process);
    }

    private function getEntityManagerStub(): EntityManager
    {
        /** @var EntityManager $stub */
        $stub = Stub::makeEmpty(EntityManager::class, [], $this);

        return $stub;
    }

    private function getAmazonThrottlingService(): AmazonThrottlingService
    {
        /** @var AmazonThrottlingService $stub */
        $stub = Stub::makeEmpty(AmazonThrottlingService::class, [], $this);

        return $stub;
    }

    private function getEncryptionServiceStub(): EncryptionService
    {
        /** @var EncryptionService $stub */
        $stub = Stub::makeEmpty(
            EncryptionService::class,
            ['decrypt' => 'decrypted-string'],
            $this
        );

        return $stub;
    }

    private function getLoggerStub(): Logger
    {
        /** @var Logger $stub */
        $stub = Stub::makeEmpty(Logger::class, [], $this);

        return $stub;
    }

    private function getContainerStub(array $services = []): Container
    {
        /** @var Container $stub */
        $stub = Stub::makeEmpty(
            Container::class,
            [
                'get' => function (string $serviceId) use ($services) {
                    return $services[$serviceId];
                },
            ],
            $this
        );

        return $stub;
    }

    private function stubAmazonClient(array $properties = []): Client
    {
        /** @var Client $stub */
        $stub = Stub::makeEmpty(Client::class, $properties, $this);

        return $stub;
    }

    private function getDataFileHandlerStub(): DataFileHandler
    {
        /** @var DataFileHandler $stub */
        $stub = Stub::makeEmpty(DataFileHandler::class, [], $this);

        return $stub;
    }
}
