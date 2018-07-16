<?php

namespace App\Command;

use App\Entity\AmazonChannelProcess;
use App\Service\AmazonMWS\AmazonChannelProcessImportOrdersService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportOrdersCommand extends ContainerAwareCommand
{
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('app_amazon_mws:orders:import')
            ->setDescription('Import Orders from Amazon MWS')
            ->addOption('process_id', 'p', InputOption::VALUE_REQUIRED)
            ->addOption('mocks_path', 'm', InputOption::VALUE_OPTIONAL, 'Define path for amazon\'s mocks responses');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processId = $input->getOption('process_id');
        $channelAmazonProcess = $this->getContainer()
            ->get('doctrine')
            ->getRepository(AmazonChannelProcess::class)
            ->find($processId);

        if ($channelAmazonProcess instanceof AmazonChannelProcess) {
            $processImportOrdersService = $this->getContainer()
                ->get(AmazonChannelProcessImportOrdersService::class);

            $processImportOrdersService->setOutput($output);
            $processImportOrdersService->execute($channelAmazonProcess);

            return;
        }

        $output->writeln(sprintf('No process found with id: %s', $processId));
    }
}
