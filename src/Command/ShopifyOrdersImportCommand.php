<?php

namespace App\Command;

use App\Entity\ShopifyStoreProcess;
use App\Service\Shopify\ShopifyStoreProcessImportOrdersService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShopifyOrdersImportCommand extends ContainerAwareCommand
{
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('shopify:orders:import')
            ->setDescription('Import Orders from Shopify store')
            ->addOption('process_id', 'p', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processId = $input->getOption('process_id');
        $process = $this->getContainer()
            ->get('doctrine')
            ->getRepository(ShopifyStoreProcess::class)
            ->find($processId);

        if ($process instanceof ShopifyStoreProcess) {
            $processImportOrdersService = $this->getContainer()
                ->get(ShopifyStoreProcessImportOrdersService::class);

            $processImportOrdersService->setOutput($output);
            $processImportOrdersService->execute($process);

            return;
        }

        $output->writeln(sprintf('No process found with id: %s', $processId));
    }
}
