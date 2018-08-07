<?php

namespace Tests\Unit\Consumer;

use App\Consumer\ShopifyStoreProcessConsumer;
use App\Entity\ShopifyStoreProcess;
use App\Service\Shopify\ShopifyStoreProcessImportOrdersService;
use App\Service\Shopify\ShopifyStoreProcessNotifier;
use Codeception\Stub\Expected;
use Codeception\TestCase\Test;
use Codeception\Util\Stub;
use Doctrine\ORM\EntityRepository;
use PhpAmqpLib\Message\AMQPMessage;
use Traits\StubEntityManagerTrait;

class ShopifyStoreProcessConsumerTest extends Test
{
    use StubEntityManagerTrait;

    public function testExecute()
    {
        $process = new ShopifyStoreProcess();
        $process->setId(150);

        $findProcess = Expected::exactly(
            2,
            function ($id) use ($process) {
                $this->assertEquals($process->getId(), $id);

                return $process;
            }
        );

        $processRepository = Stub::makeEmpty(EntityRepository::class, ['find' => $findProcess], $this);
        $entityManager = $this->stubEntityManager([ShopifyStoreProcess::class => $processRepository]);

        $executeImport = Expected::once(function (ShopifyStoreProcess $processToImportFrom) use ($process) {
            $this->assertEquals($process->getId(), $processToImportFrom->getId());
        });
        /** @var ShopifyStoreProcessImportOrdersService $processImportOrdersService */
        $processImportOrdersService = Stub::makeEmpty(
            ShopifyStoreProcessImportOrdersService::class,
            ['execute' => $executeImport],
            $this
        );

        $notify = Expected::once(function ($processToNotifyAbout) use ($process) {

        });
        /** @var ShopifyStoreProcessNotifier $notifier */
        $notifier = Stub::makeEmpty(ShopifyStoreProcessNotifier::class, ['notify' => $notify]);

        $consumer = new ShopifyStoreProcessConsumer($entityManager, $processImportOrdersService, $notifier);

        $process->setStatus(ShopifyStoreProcess::STATUS_IDLE);
        $consumer->execute(new AMQPMessage($process->getId()));
        $process->setStatus(ShopifyStoreProcess::STATUS_IN_PROGRESS);
        $consumer->execute(new AMQPMessage($process->getId()));
    }

    public function testExecuteForNonExistingProcess()
    {
        $process = new ShopifyStoreProcess();
        $process->setId(150);

        $processRepository = Stub::makeEmpty(EntityRepository::class, ['find' => null], $this);
        $entityManager = $this->stubEntityManager([ShopifyStoreProcess::class => $processRepository]);

        $executeImport = Expected::never();
        /** @var ShopifyStoreProcessImportOrdersService $processImportOrdersService */
        $processImportOrdersService = Stub::makeEmpty(
            ShopifyStoreProcessImportOrdersService::class,
            ['execute' => $executeImport],
            $this
        );

        $notify = Expected::never();
        /** @var ShopifyStoreProcessNotifier $notifier */
        $notifier = Stub::makeEmpty(ShopifyStoreProcessNotifier::class, ['notify' => $notify]);

        $consumer = new ShopifyStoreProcessConsumer($entityManager, $processImportOrdersService, $notifier);

        $process->setStatus(ShopifyStoreProcess::STATUS_IDLE);
        $consumer->execute(new AMQPMessage($process->getId()));
    }
}
