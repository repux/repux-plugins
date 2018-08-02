<?php

namespace Tests\Unit\Consumer;

use App\Consumer\AmazonChannelProcessConsumer;
use App\Entity\AmazonChannel;
use App\Entity\AmazonChannelProcess;
use App\Service\AmazonMWS\AmazonChannelProcessImportOrdersService;
use App\Service\AmazonMWS\AmazonChannelProcessNotifier;
use Codeception\Stub\Expected;
use Codeception\TestCase\Test;
use Codeception\Util\Stub;
use Doctrine\ORM\EntityRepository;
use PhpAmqpLib\Message\AMQPMessage;
use Traits\StubEntityManagerTrait;

class AmazonChannelProcessConsumerTest extends Test
{
    use StubEntityManagerTrait;

    public function testExecute()
    {
        $channel = new AmazonChannel();
        $process = new AmazonChannelProcess();
        $process->setAmazonChannel($channel);
        $process->setId(150);

        $findProcess = Expected::exactly(
            2,
            function ($id) use ($process) {
                $this->assertEquals($process->getId(), $id);

                return $process;
            }
        );

        $processRepository = Stub::makeEmpty(EntityRepository::class, ['find' => $findProcess], $this);
        $entityManager = $this->stubEntityManager([AmazonChannelProcess::class => $processRepository]);

        $executeImport = Expected::once(function (AmazonChannelProcess $processToImportFrom) use ($process) {
            $this->assertEquals($process->getId(), $processToImportFrom->getId());
        });
        /** @var AmazonChannelProcessImportOrdersService $processImportOrdersService */
        $processImportOrdersService = Stub::makeEmpty(
            AmazonChannelProcessImportOrdersService::class,
            ['execute' => $executeImport],
            $this
        );

        $notify = Expected::once(function ($processToNotifyAbout) use ($process) {

        });
        /** @var AmazonChannelProcessNotifier $notifier */
        $notifier = Stub::makeEmpty(AmazonChannelProcessNotifier::class, ['notify' => $notify]);

        $consumer = new AmazonChannelProcessConsumer($entityManager, $processImportOrdersService, $notifier);

        $process->setType(AmazonChannelProcess::TYPE_IMPORT_ORDERS);
        $channel->setStatus(AmazonChannelProcess::STATUS_IDLE);
        $consumer->execute(new AMQPMessage($process->getId()));
        $channel->setStatus(AmazonChannelProcess::STATUS_IN_PROGRESS);
        $consumer->execute(new AMQPMessage($process->getId()));
    }

    public function testExecuteForNonExistingProcess()
    {
        $channel = new AmazonChannel();
        $process = new AmazonChannelProcess();
        $process->setAmazonChannel($channel);
        $process->setId(150);

        $processRepository = Stub::makeEmpty(EntityRepository::class, ['find' => null], $this);
        $entityManager = $this->stubEntityManager([AmazonChannelProcess::class => $processRepository]);

        $executeImport = Expected::never();
        /** @var AmazonChannelProcessImportOrdersService $processImportOrdersService */
        $processImportOrdersService = Stub::makeEmpty(
            AmazonChannelProcessImportOrdersService::class,
            ['execute' => $executeImport],
            $this
        );

        $notify = Expected::never();
        /** @var AmazonChannelProcessNotifier $notifier */
        $notifier = Stub::makeEmpty(AmazonChannelProcessNotifier::class, ['notify' => $notify]);

        $consumer = new AmazonChannelProcessConsumer($entityManager, $processImportOrdersService, $notifier);

        $process->setType(AmazonChannelProcess::TYPE_IMPORT_ORDERS);
        $channel->setStatus(AmazonChannelProcess::STATUS_IDLE);
        $consumer->execute(new AMQPMessage($process->getId()));
    }
}
