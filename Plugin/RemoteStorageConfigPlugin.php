<?php

declare(strict_types=1);

namespace DainoKit\Storage\Plugin;

use DainoKit\Storage\Helper\Config;
use Magento\RemoteStorage\Model\Config as RemoteStorageConfig;

class RemoteStorageConfigPlugin
{
    public function __construct(
        private readonly Config $config
    ) {}

    /**
     * Override remote storage driver when DainoKit is enabled
     */
    public function afterGetDriver(RemoteStorageConfig $subject, $result): string
    {
        if ($this->config->isEnabled() && $this->config->getBucket()) {
            return 'aws-s3';
        }
        return (string) $result;
    }

    /**
     * Override remote storage config with DainoKit settings
     */
    public function afterGetConfig(RemoteStorageConfig $subject, $result): array
    {
        if (!$this->config->isEnabled() || !$this->config->getBucket()) {
            return $result;
        }

        return [
            'bucket' => $this->config->getBucket(),
            'region' => $this->config->getRegion(),
            'endpoint' => [
                'url' => $this->config->getEndpoint(),
            ],
            'credentials' => [
                'key' => $this->config->getAccessKey(),
                'secret' => $this->config->getSecretKey(),
            ],
            'path_style' => $this->config->usePathStyle(),
        ];
    }

    /**
     * Override prefix (object key prefix in bucket)
     */
    public function afterGetPrefix(RemoteStorageConfig $subject, $result): string
    {
        if ($this->config->isEnabled() && $this->config->getBucket()) {
            return '';
        }
        return (string) $result;
    }
}
