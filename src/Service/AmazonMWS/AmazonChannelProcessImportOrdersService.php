<?php

namespace App\Service\AmazonMWS;

use App\Entity\AmazonChannelProcess;
use App\Entity\DataFile;
use App\Handler\DataFileHandler;
use App\Service\ArrayToCsvService;
use App\Service\EncryptionService;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Client as OrdersClient;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\ClientException as AmazonOrdersException;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Model\ListOrdersByNextTokenRequest;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Model\ListOrdersByNextTokenResult;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Model\ListOrdersRequest;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Model\ListOrdersResult;
use App\Service\AmazonMWS\sdk\MarketplaceWebServiceOrders\Model\Order;
use App\Entity\AmazonChannel;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AmazonChannelProcessImportOrdersService
{
    const TEMP_FILE_PREFIX = 'amazon_';
    const ORDERS_FILE_MIME_TYPE = 'text/csv';
    const THROTTLING_SLEEP_SECONDS = 15;

    private $entityManager;

    private $amazonThrottlingService;

    private $encryptionService;

    private $container;

    private $logger;

    private $arrayToCsvService;

    private $dataFileHandler;

    private $amazonUrlService;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var AmazonChannel
     */
    private $channel;

    /**
     * @var AmazonChannelProcess
     */
    private $process;

    /** @var OrdersClient */
    private $ordersClient;

    /** @var  string $nextToken */
    private $nextToken;

    private $filepath;

    public function __construct(
        EntityManager $entityManager,
        AmazonThrottlingService $amazonThrottlingService,
        EncryptionService $encryptionService,
        LoggerInterface $logger,
        ContainerInterface $container,
        ArrayToCsvService $arrayToCsvService,
        DataFileHandler $dataFileHandler,
        AmazonUrlService $amazonUrlService
    ) {
        $this->entityManager = $entityManager;
        $this->amazonThrottlingService = $amazonThrottlingService;
        $this->encryptionService = $encryptionService;
        $this->logger = $logger;
        $this->container = $container;
        $this->arrayToCsvService = $arrayToCsvService;
        $this->dataFileHandler = $dataFileHandler;
        $this->amazonUrlService = $amazonUrlService;
        $this->output = new NullOutput();
    }

    public function execute(AmazonChannelProcess $process)
    {
        $this->filepath = tempnam(sys_get_temp_dir(), self::TEMP_FILE_PREFIX);

        $this->process = $process;
        $this->channel = $process->getAmazonChannel();

        try {
            $this->doImport($this->channel);
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            @unlink($this->filepath);
        }
    }

    private function doImport(AmazonChannel $channel)
    {
        $channel->setStatus(AmazonChannel::STATUS_IN_PROGRESS);
        $this->entityManager->flush();

        $this->writeln(sprintf('start: %s', $this->filepath));

        $regionAbbr = AmazonChannel::getMarketplaceRegionByMarketplaceId($channel->getMarketplaceId());

        $this->ordersClient = $this->container->get("app_amazon_mws.client.orders.{$regionAbbr}");
        $this->ordersClient->setConfig([
            'ServiceURL' => sprintf('%s/Orders/2013-09-01', $this->amazonUrlService->getServiceUrl($channel))
        ]);
        $this->ordersClient->setOutput($this->output);

        $repeatRequest = false;
        $hasOrders = false;
        $this->nextToken = null;
        
        do {
            try {
                $lastSyncAt = new \DateTime('now');
                $lastSyncAt->setTimezone(new \DateTimeZone('UTC'));

                do {
                    $hasOrders |= $this->processOrders();
                } while (!empty($this->nextToken));
                $repeatRequest = false;
            } catch (AmazonOrdersException $ex) {
                if ($ex->getStatusCode() == 503) {
                    $repeatRequest = true;
                    $this->writeln(sprintf('Request throttled by amazon'));
                    sleep(self::THROTTLING_SLEEP_SECONDS);
                }
                $this->writeln(sprintf('Error: %s', $ex->getMessage()));
            } catch (\Exception $ex) {
                $this->writeln(sprintf('Error: %s', $ex->getMessage()));

                return;
            }
        } while ($repeatRequest);

        if ($hasOrders) {
            $dataFile = $this->uploadFile($this->channel, self::ORDERS_FILE_MIME_TYPE);

            $this->process->setStatus(AmazonChannelProcess::STATUS_SUCCESS);
            $this->process->setData(\json_encode([
                    'dataFileId' => $dataFile->getId(),
                ]
            ));
        } else {
            $this->process->setStatus(AmazonChannelProcess::STATUS_EMPTY_RESPONSE);
        }

        $this->channel->setStatus(AmazonChannel::STATUS_IDLE);
        $this->entityManager->flush();
    }

    private function processOrders(): bool
    {
        if (empty($this->nextToken)) {
            /** @var ListOrdersResult $listOrdersResult */
            $listOrdersResult = $this->getListOrderResult();
        } else {
            /** @var ListOrdersByNextTokenResult $listOrdersResult */
            $listOrdersResult = $this->getListOrderByNextTokenResult();
        }

        $ordersProcessed = 0;
        if ($listOrdersResult) {
            $orders = $listOrdersResult->getOrders();
            $ordersTotal = count($orders);

            /** @var Order $order */
            while (!empty($orders)) {
                $order = reset($orders);
                try {
                    $data = $this->processOrder($order);
                    $this->arrayToCsvService->save($this->filepath, $data);
                    $ordersProcessed++;
                    unset($orders[key($orders)]);
                } catch (\Exception $ex) {
                    $this->writeln(sprintf('Error: %s', $ex->getMessage()));

                    throw $ex;
                }
            }

            $this->writeln("{$ordersProcessed} of {$ordersTotal} orders imported");
        }

        return $ordersProcessed > 0;
    }

    private function getListOrderResult()
    {
        $parameters = [
            'SellerId' => $this->channel->getMerchantId(),
            'MWSAuthToken' => $this->getApiToken($this->channel),
            'MarketplaceId' => $this->channel->getMarketplaceId(),
            'CreatedAfter' => $this->getPeriodFromParameters()->format('c'),
        ];

        $request = new ListOrdersRequest($parameters);

        $this->amazonThrottlingService->processThrottling(
            "ListOrdersRequest:{$this->channel->getMerchantId()}",
            $this->channel->getId(),
            ListOrdersRequest::RESTORE_RATE,
            ListOrdersRequest::MAX_QUOTA
        );

        $response = $this->ordersClient->listOrders($request);
        /** @var ListOrdersResult $orders */
        $orders = $response->getListOrdersResult();

        if ($orders) {
            $this->nextToken = $orders->getNextToken();
        }

        return $orders;
    }

    private function getListOrderByNextTokenResult()
    {
        $this->output->writeln(sprintf('Process NextToken: %s', $this->nextToken));
        $parameters = [
            'SellerId' => $this->channel->getMerchantId(),
            'MWSAuthToken' => $this->getApiToken($this->channel),
            'NextToken' => $this->nextToken,
        ];

        $request = new ListOrdersByNextTokenRequest($parameters);

        $this->amazonThrottlingService->processThrottling(
            "ListOrdersRequest:{$this->channel->getMerchantId()}",
            $this->channel->getId(),
            ListOrdersRequest::RESTORE_RATE,
            ListOrdersRequest::MAX_QUOTA
        );

        $response = $this->ordersClient->listOrdersByNextToken($request);
        $orders = $response->getListOrdersByNextTokenResult();

        if ($orders) {
            $this->nextToken = $orders->getNextToken();
        }

        return $orders;
    }

    private function processOrder(Order $order): array
    {
        $this->writeln(sprintf('Importing Order#%s"', $order->getAmazonOrderId()));

        $data = [];

        $data['fulfilmentChannel'] = $order->getFulfillmentChannel();
        $data['salesChannel'] = $order->getSalesChannel();

        $shippingAddress = $order->getShippingAddress();
        if (!empty($shippingAddress)) {
            $data['ShippingAddressCity'] = $order->getShippingAddress()->getCity();
            $data['ShippingAddressPostalCode'] = $order->getShippingAddress()->getPostalCode();
            $data['ShippingAddressCountryCode'] = $order->getShippingAddress()->getCountryCode();
        }

        if ($order->getOrderTotal()) {
            $data['orderTotalAmount'] = $order->getOrderTotal()->getAmount();
            $data['orderTotalCurrencyCode'] = $order->getOrderTotal()->getCurrencyCode();
        }

        $data['numbersOfItemsShipped'] = $order->getNumberOfItemsShipped();
        $data['numbersOfItemsUnshipped'] = $order->getNumberOfItemsUnshipped();
        $data['paymentMethod'] = $order->getPaymentMethod();
        $data['marketplaceId'] = $order->getMarketplaceId();
        $data['orderType'] = $order->getOrderType();
        $data['isPrime'] = $order->getIsPrime();

        return $data;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    private function writeln(string $message)
    {
        $taggedMessage = sprintf('[amazon][process]:%s %s', $this->process->getId(), $message);
        $this->output->writeln($taggedMessage);
        $this->logger->info($taggedMessage);
    }

    protected function getApiToken(AmazonChannel $channel): string
    {
        return $this->encryptionService->decrypt($channel->getApiToken());
    }

    private function uploadFile(AmazonChannel $channel, string $mimeType = null): DataFile
    {
        $user = $channel->getUser();
        $now = new \DateTime();
        $filename = sprintf('Amazon_sales_%s.csv', $now->format('YmdHis'));

        $dataFile = new DataFile();
        $uploadedFile = new UploadedFile($this->filepath, $filename, $mimeType);
        $dataFile->setUploadedFile($uploadedFile);
        $dataFile->setOrigin(DataFile::ORIGIN_SHOPIFY);

        $this->dataFileHandler->create($dataFile, $user);

        return $dataFile;
    }

    private function getPeriodFromParameters(): \DateTime
    {
        $parameters = \json_decode($this->process->getParameters(), true);
        $period = $parameters['period'] ?? -1;

        switch ($period) {
            case AmazonChannelProcess::PARAMETER_PERIOD_LAST_MONTH:
                $date = new \DateTime();
                $interval = new \DateInterval('P1M');
                $date->sub($interval);
                break;
            case AmazonChannelProcess::PARAMETER_PERIOD_LAST_QUARTER:
                $date = new \DateTime();
                $interval = new \DateInterval('P4M');
                $date->sub($interval);
                break;
            case AmazonChannelProcess::PARAMETER_PERIOD_ALL:
                $date = new \DateTime('1970-01-01 00:00:00');
                break;
            default:
                $date = new \DateTime('first day of this month');
                break;
        }

        return $date;
    }
}
