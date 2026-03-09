<?php

declare(strict_types=1);

namespace DainoKit\Storage\Model\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\DeploymentConfig\Writer as DeploymentConfigWriter;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AfterSave extends Value
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        private readonly DeploymentConfigWriter $deploymentConfigWriter,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave(): static
    {
        parent::afterSave();
        $this->syncToEnvPhp();
        return $this;
    }

    private function syncToEnvPhp(): void
    {
        try {
            $enabled = (bool) $this->getFieldsetDataValue('enabled');

            if (!$enabled) {
                // Remove remote storage config
                $this->deploymentConfigWriter->saveConfig(
                    [ConfigFilePool::APP_ENV => ['remote_storage' => ['driver' => '']]],
                    true
                );
                return;
            }

            $bucket = (string) $this->getFieldsetDataValue('bucket');
            $accessKey = (string) $this->getFieldsetDataValue('access_key');
            $secretKey = (string) $this->getFieldsetDataValue('secret_key');
            $endpoint = (string) ($this->getFieldsetDataValue('endpoint') ?: 'https://s3.dainokit.com');
            $region = (string) ($this->getFieldsetDataValue('region') ?: 'auto');
            $pathStyle = (bool) $this->getFieldsetDataValue('path_style');

            // Secret key field sends '******' when unchanged — keep existing
            if (!$secretKey || $secretKey === '******') {
                return;
            }

            if (!$bucket || !$accessKey) {
                return;
            }

            $this->deploymentConfigWriter->saveConfig(
                [ConfigFilePool::APP_ENV => [
                    'remote_storage' => [
                        'driver' => 'aws-s3',
                        'config' => [
                            'bucket' => $bucket,
                            'region' => $region,
                            'endpoint' => [
                                'url' => $endpoint,
                            ],
                            'credentials' => [
                                'key' => $accessKey,
                                'secret' => $secretKey,
                            ],
                            'path_style' => $pathStyle,
                        ],
                    ],
                ]],
                true
            );
        } catch (\Exception $e) {
            $this->_logger->error('DainoKit Storage: Failed to sync config to env.php: ' . $e->getMessage());
        }
    }
}
