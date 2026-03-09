<?php

declare(strict_types=1);

namespace DainoKit\Storage\Controller\Adminhtml\Connection;

use DainoKit\Storage\Model\ConnectionTest;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;

class Test extends Action
{
    public const ADMIN_RESOURCE = 'DainoKit_Storage::config';

    public function __construct(
        Context $context,
        private readonly ConnectionTest $connectionTest,
        private readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        $result = $this->jsonFactory->create();

        $endpoint  = (string) $this->getRequest()->getParam('endpoint');
        $bucket    = (string) $this->getRequest()->getParam('bucket');
        $region    = (string) $this->getRequest()->getParam('region');
        $accessKey = (string) $this->getRequest()->getParam('access_key');
        $secretKey = (string) $this->getRequest()->getParam('secret_key');

        if (!$endpoint || !$bucket || !$accessKey || !$secretKey) {
            return $result->setData([
                'success' => false,
                'message' => 'Please fill in all required fields (endpoint, bucket, access key, secret key).',
            ]);
        }

        $testResult = $this->connectionTest->execute(
            $endpoint,
            $bucket,
            $region ?: 'auto',
            $accessKey,
            $secretKey
        );

        return $result->setData($testResult);
    }
}
