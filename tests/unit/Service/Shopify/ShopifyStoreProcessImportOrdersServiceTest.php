<?php

namespace Tests\Unit\Service\Shopify;

use App\Entity\ShopifyStore;
use App\Entity\ShopifyStoreProcess;
use App\Handler\DataFileHandler;
use App\Service\ArrayToCsvService;
use App\Service\Shopify\ShopifyStoreProcessImportOrdersService;
use App\Shopify\Api\Endpoint\Order;
use App\Shopify\Api\ShopifyApi;
use App\Shopify\Api\ShopifyApiFactory;
use Codeception\Stub;
use Codeception\TestCase\Test;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;

class ShopifyStoreProcessImportOrdersServiceTest extends Test
{
    const PROCESS_PARAMETERS = '{"created_at_from": "2014-01-01 00:00:00", "created_at_to": "2015-01-01 00:01:11"}';

    public function testProcessParametersImport()
    {
        $getForStore = Stub\Expected::once(function ($store) {
            return $this->getShopifyApiStub([
                'Order' => $this->getShopifyOrderEndpointStub([
                    'findAll' => Stub\Expected::once(function ($query) {
                        $this->assertEquals('2014-01-01T00:00:00+00:00', $query['created_at_min']);
                        $this->assertEquals('2015-01-01T00:01:11+00:00', $query['created_at_max']);

                        return [];
                    })
                ]),
            ]);
        });

        $service = new ShopifyStoreProcessImportOrdersService(
            $this->getEntityManagerStub(),
            $this->getLoggerStub(),
            $this->getShopifyApiFactoryStub(['getForStore' => $getForStore]),
            new ArrayToCsvService(),
            $this->getDataFileHandlerStub()
        );

        $store = new ShopifyStore();
        $store->setId(1);

        $process = new ShopifyStoreProcess();
        $process->setShopifyStore($store);
        $process->setParameters(self::PROCESS_PARAMETERS);

        $service->execute($process);
    }

    private function getEntityManagerStub(): EntityManager
    {
        /** @var EntityManager $stub */
        $stub = Stub::makeEmpty(EntityManager::class, [], $this);

        return $stub;
    }

    private function getLoggerStub(): Logger
    {
        /** @var Logger $stub */
        $stub = Stub::makeEmpty(Logger::class, [], $this);

        return $stub;
    }

    private function getShopifyApiFactoryStub(array $properties = []): ShopifyApiFactory
    {
        /** @var ShopifyApiFactory $stub */
        $stub = Stub::makeEmpty(ShopifyApiFactory::class, $properties, $this);

        return $stub;
    }

    private function getShopifyApiStub(array $properties = []): ShopifyApi
    {
        /** @var ShopifyApi $stub */
        $stub = Stub::makeEmpty(ShopifyApi::class, $properties, $this);

        return $stub;
    }

    private function getShopifyOrderEndpointStub(array $properties = []): Order
    {
        /** @var Order $stub */
        $stub = Stub::makeEmpty(Order::class, $properties, $this);

        return $stub;
    }

    private function getDataFileHandlerStub(): DataFileHandler
    {
        /** @var DataFileHandler $stub */
        $stub = Stub::makeEmpty(DataFileHandler::class, [], $this);

        return $stub;
    }
}
