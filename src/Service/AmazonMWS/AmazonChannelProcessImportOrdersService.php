<?php

namespace App\Service\AmazonMWS;

use App\Entity\ChannelAmazonProcess;
use App\Entity\DataFile;
use App\Exception\AmazonThrottleException;
use App\Handler\DataFileHandler;
use App\Service\ArrayToCsvService;
use App\Service\EncryptionService;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Client as OrdersClient;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\ClientException as AmazonOrdersException;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\ClientException;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrdersByNextTokenRequest;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrdersByNextTokenResult;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrdersRequest;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\ListOrdersResult;
use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model\Order;
use App\Entity\ChannelAmazon;
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

    private $amazonStubService;

    private $entityManager;

    private $amazonThrottlingService;

    private $encryptionService;

    private $container;

    private $logger;

    private $arrayToCsvService;

    private $dataFileHandler;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ChannelAmazon
     */
    private $channel;

    /**
     * @var ChannelAmazonProcess
     */
    private $process;

    /** @var OrdersClient */
    private $ordersClient;

    /** @var  string $nextToken */
    private $nextToken;

    private $filepath;

    public function __construct(
        EntityManager $entityManager,
        AmazonStubService $amazonStubService,
        AmazonThrottlingService $amazonThrottlingService,
        EncryptionService $encryptionService,
        LoggerInterface $logger,
        ContainerInterface $container,
        ArrayToCsvService $arrayToCsvService,
        DataFileHandler $dataFileHandler
    ) {
        $this->entityManager = $entityManager;
        $this->amazonStubService = $amazonStubService;
        $this->amazonThrottlingService = $amazonThrottlingService;
        $this->encryptionService = $encryptionService;
        $this->logger = $logger;
        $this->container = $container;
        $this->arrayToCsvService = $arrayToCsvService;
        $this->dataFileHandler = $dataFileHandler;
        $this->output = new NullOutput();
    }

    public function execute(ChannelAmazonProcess $process)
    {
        $this->filepath = tempnam(sys_get_temp_dir(), self::TEMP_FILE_PREFIX);

        $this->process = $process;
        $this->channel = $process->getChannelAmazon();

        try {
            $this->doImport($this->channel);
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            @unlink($this->filepath);
        }
    }

    private function doImport(ChannelAmazon $channel)
    {
        $channel->setStatus(ChannelAmazon::STATUS_IN_PROGRESS);
        $this->entityManager->flush();

        $this->writeln(sprintf('start: %s', $this->filepath));

        $regionAbbr = ChannelAmazon::getMarketplaceRegionByMarketplaceId($channel->getMarketplaceId());

        $this->ordersClient = $this->container->get("app_amazon_mws.client.orders.{$regionAbbr}");
        $this->ordersClient->setConfig(['ServiceURL' => $channel->getServiceUrl().'/Orders/2013-09-01']);
        $this->ordersClient->setOutput($this->output);

        $repeatRequest = false;
        do {
            try {
                $this->nextToken = null;

                $lastSyncAt = new \DateTime('now');
                $lastSyncAt->setTimezone(new \DateTimeZone('UTC'));

                do {
                    $this->processOrders();
                } while (!empty($this->nextToken));
                $repeatRequest = false;
            } catch (AmazonOrdersException $ex) {
                if ($ex->getStatusCode() == 503) {
                    $repeatRequest = true;
                    $this->writeln(sprintf('Request throttled by amazon'));
                    sleep(self::THROTTLING_SLEEP_SECONDS); // sorry i'm bad bad bad programmer :(
                }
                $this->writeln(sprintf('Error: %s', $ex->getMessage()));
            } catch (\Exception $ex) {
                $repeatRequest = false;
                $this->writeln(sprintf('Error: %s', $ex->getMessage()));
            }
        } while ($repeatRequest);
    }

    private function processOrders()
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
                } catch (AmazonThrottleException $ex) {
                    //do nothing, just wait
                    $this->writeln('throttled internal');
                    sleep(self::THROTTLING_SLEEP_SECONDS);
                } catch (AmazonOrdersException $ex) {
                    /** @var ClientException $ex */
                    if ($ex->getStatusCode() == 503) {
                        $this->output->writeln(sprintf('Channel %s has been throttled', $this->channel->getId()));
                        sleep(self::THROTTLING_SLEEP_SECONDS);
                    }
                    $this->writeln(sprintf('Error: %s', $ex->getMessage()));
                } catch (\Exception $ex) {
                    $orders = [];
                    $this->writeln(sprintf('Error: %s', $ex->getMessage()));
                }
            }

            $this->writeln("{$ordersProcessed} of {$ordersTotal} orders imported");
        }

        if ($ordersProcessed) {
            $dataFile = $this->uploadFile($this->channel, self::ORDERS_FILE_MIME_TYPE);

            $this->process->setStatus(ChannelAmazonProcess::STATUS_SUCCESS);
            $this->process->setData(\json_encode([
                    'dataFileId' => $dataFile->getId(),
                ]
            ));
        } else {
            $this->process->setStatus(ChannelAmazonProcess::STATUS_EMPTY_RESPONSE);
        }

        $this->channel->setStatus(ChannelAmazon::STATUS_IDLE);
        $this->entityManager->flush();
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
        $this->nextToken = $orders->getNextToken();

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
        $this->nextToken = $orders->getNextToken();

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

    public function setMocksPath(string $mocksPath)
    {
        $this->amazonStubService->init($mocksPath);
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

    protected function getApiToken(ChannelAmazon $channel): string
    {
        return $this->encryptionService->decrypt($channel->getApiToken());
    }

    private function uploadFile(ChannelAmazon $channel, string $mimeType = null): DataFile
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
            case ChannelAmazonProcess::PARAMETER_PERIOD_LAST_MONTH:
                $date = new \DateTime();
                $interval = new \DateInterval('P1M');
                $date->sub($interval);
                break;
            case ChannelAmazonProcess::PARAMETER_PERIOD_LAST_QUARTER:
                $date = new \DateTime();
                $interval = new \DateInterval('P4M');
                $date->sub($interval);
                break;
            case ChannelAmazonProcess::PARAMETER_PERIOD_ALL:
                $date = new \DateTime('1970-01-01 00:00:00');
                break;
            default:
                $date = new \DateTime('first day of this month');
                break;
        }

        return $date;
    }
}
