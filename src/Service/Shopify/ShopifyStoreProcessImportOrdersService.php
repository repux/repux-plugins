<?php

namespace App\Service\Shopify;

use App\Entity\DataFile;
use App\Entity\ShopifyStore;
use App\Entity\ShopifyStoreProcess;
use App\Handler\DataFileHandler;
use App\Service\ArrayToCsvService;
use App\Shopify\Api\GenericResource;
use App\Shopify\Api\ShopifyApiFactory;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ShopifyStoreProcessImportOrdersService
{
    const TEMP_FILE_PREFIX = 'shopify_';
    const ORDERS_FILE_MIME_TYPE = 'text/csv';

    private $orderFields = [
        'id',
        'created_at',
        'shipping_address',
        'total_price',
        'currency',
        'fulfillments',
        'source_name',
    ];

    private $orderFieldNestedProperties = [
        'shipping_address' => [
            'country',
            'city',
            'zip',
        ],
        'fulfillment' => [
            'tracking_company',
        ],
    ];

    private $entityManager;

    private $logger;

    private $shopifyApiFactory;

    private $arrayToCsvService;

    private $dataFileHandler;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ShopifyStore
     */
    private $store;

    /**
     * @var ShopifyStoreProcess
     */
    private $process;

    private $filepath;

    public function __construct(
        EntityManager $entityManager,
        LoggerInterface $logger,
        ShopifyApiFactory $shopifyApiFactory,
        ArrayToCsvService $arrayToCsvService,
        DataFileHandler $dataFileHandler
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->shopifyApiFactory = $shopifyApiFactory;
        $this->arrayToCsvService = $arrayToCsvService;
        $this->dataFileHandler = $dataFileHandler;
        $this->output = new NullOutput();
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute(ShopifyStoreProcess $process)
    {
        $this->filepath = tempnam(sys_get_temp_dir(), self::TEMP_FILE_PREFIX);

        $this->process = $process;
        $this->store = $process->getShopifyStore();

        try {
            $this->doImport($this->store);
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            @unlink($this->filepath);
        }
    }

    private function doImport(ShopifyStore $store)
    {
        $this->writeln(sprintf('start: %s', $this->filepath));

        try {
            $query = [
                'status' => 'closed',
                'fields' => implode(',', $this->orderFields),
                'created_at_min' => $this->getPeriodFromParameters()->format('c'),
            ];

            $api = $this->shopifyApiFactory->getForStore($store);
            $orders = $api->Order->findAll($query);

            $this->processOrders($orders);
        } catch (\Exception $exception) {
            $this->writeln(sprintf('Error: %s', $exception->getMessage()));

            throw $exception;
        }
    }

    private function writeln(string $message)
    {
        $taggedMessage = sprintf('[shopify][process]:%s %s', $this->process->getId(), $message);
        $this->output->writeln($taggedMessage);
        $this->logger->info($taggedMessage);
    }

    private function getPeriodFromParameters(): \DateTime
    {
        $parameters = \json_decode($this->process->getParameters(), true);
        $period = $parameters['period'] ?? -1;

        switch ($period) {
            case ShopifyStoreProcess::PARAMETER_PERIOD_LAST_MONTH:
                $date = new \DateTime();
                $interval = new \DateInterval('P1M');
                $date->sub($interval);
                break;
            case ShopifyStoreProcess::PARAMETER_PERIOD_LAST_QUARTER:
                $date = new \DateTime();
                $interval = new \DateInterval('P4M');
                $date->sub($interval);
                break;
            case ShopifyStoreProcess::PARAMETER_PERIOD_ALL:
                $date = new \DateTime('1970-01-01 00:00:00');
                break;
            default:
                $date = new \DateTime('first day of this month');
                break;
        }

        return $date;
    }

    private function processOrders(array $orders = null)
    {
        $ordersProcessed = 0;
        $ordersTotal = count($orders);

        while (!empty($orders)) {
            $order = reset($orders);
            try {
                $data = $this->processOrder($order);
                $this->arrayToCsvService->save($this->filepath, $data);
                $ordersProcessed++;
                unset($orders[key($orders)]);
            } catch (\Exception $ex) {
                $orders = [];
                $this->writeln(sprintf('Error: %s', $ex->getMessage()));
            }
        }

        $this->writeln("{$ordersProcessed} of {$ordersTotal} orders imported");

        if ($ordersProcessed) {
            $dataFile = $this->uploadFile($this->store, self::ORDERS_FILE_MIME_TYPE);

            $this->process->setStatus(ShopifyStoreProcess::STATUS_SUCCESS);
            $this->process->setData(\json_encode(['dataFileId' => $dataFile->getId()]));
        } else {
            $this->process->setStatus(ShopifyStoreProcess::STATUS_EMPTY_RESPONSE);
        }

        $this->entityManager->flush();
    }

    private function processOrder(GenericResource $order): array
    {
        $this->writeln(sprintf('Importing Order#%s"', $order->get('id')));

        $data = $order->toArray();

        foreach ($this->orderFieldNestedProperties['shipping_address'] as $property) {
            $data[sprintf('shippingAddress%s', ucfirst($property))] = '';
        }
        $data['fulfillmentTrackingCompanies'] = '';

        if (!empty($data['shipping_address'])) {
            $shippingAddress = array_intersect_key(
                $data['shipping_address'], array_flip($this->orderFieldNestedProperties['shipping_address'])
            );

            foreach ($this->orderFieldNestedProperties['shipping_address'] as $property) {
                $data[sprintf('shippingAddress%s', ucfirst($property))] = $shippingAddress[$property];
            }
        }

        if (!empty($data['fulfillments'])) {
            $fulfillmentTrackingCompanies = [];

            foreach ($data['fulfillments'] as $fulfillment) {
                $fulfillmentTrackingCompanies[] = implode(
                    ',',
                    array_intersect_key($fulfillment, array_flip($this->orderFieldNestedProperties['fulfillment']))
                );
            }

            $data['fulfillmentTrackingCompanies'] = implode(',', $fulfillmentTrackingCompanies);
        }

        unset($data['id'], $data['shipping_address'], $data['fulfillments']);

        return $data;
    }

    private function uploadFile(ShopifyStore $store, string $mimeType = null): DataFile
    {
        $user = $store->getUser();
        $now = new \DateTime();
        $filename = sprintf('shopify_sales_%s.csv', $now->format('YmdHis'));

        $dataFile = new DataFile();
        $uploadedFile = new UploadedFile($this->filepath, $filename, $mimeType);
        $dataFile->setUploadedFile($uploadedFile);
        $dataFile->setOrigin(DataFile::ORIGIN_SHOPIFY);

        $this->dataFileHandler->create($dataFile, $user);

        return $dataFile;
    }
}
