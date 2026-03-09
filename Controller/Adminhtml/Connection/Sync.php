<?php

declare(strict_types=1);

namespace DainoKit\Storage\Controller\Adminhtml\Connection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Shell;

class Sync extends Action
{
    public const ADMIN_RESOURCE = 'DainoKit_Storage::config';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly Shell $shell
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $magentoRoot = BP;
            $php = PHP_BINARY;
            $output = $this->shell->execute(
                '%s %s/bin/magento remote-storage:sync 2>&1',
                [$php, $magentoRoot]
            );

            return $result->setData([
                'success' => true,
                'message' => $output ?: 'Media sync completed successfully.',
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ]);
        }
    }
}
