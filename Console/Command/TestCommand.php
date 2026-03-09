<?php

declare(strict_types=1);

namespace DainoKit\Storage\Console\Command;

use DainoKit\Storage\Helper\Config;
use DainoKit\Storage\Model\ConnectionTest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    public function __construct(
        private readonly Config $config,
        private readonly ConnectionTest $connectionTest,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('dainokit:storage:test')
            ->setDescription('Test the DainoKit S3 storage connection using saved configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $endpoint  = $this->config->getEndpoint();
        $bucket    = $this->config->getBucket();
        $region    = $this->config->getRegion();
        $accessKey = $this->config->getAccessKey();
        $secretKey = $this->config->getSecretKey();
        $pathStyle = $this->config->usePathStyle();

        if (!$endpoint || !$bucket || !$accessKey || !$secretKey) {
            $output->writeln('<error>Missing required configuration. Please set endpoint, bucket, access key, and secret key in admin.</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('Testing connection to <info>%s</info> bucket <info>%s</info>...', $endpoint, $bucket));

        $result = $this->connectionTest->execute(
            $endpoint,
            $bucket,
            $region,
            $accessKey,
            $secretKey,
            $pathStyle
        );

        if ($result['success']) {
            $output->writeln('<info>' . $result['message'] . '</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>' . $result['message'] . '</error>');
        return Command::FAILURE;
    }
}
