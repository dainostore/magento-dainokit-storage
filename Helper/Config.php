<?php

declare(strict_types=1);

namespace DainoKit\Storage\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    private const XML_PATH_PREFIX = 'dainokit_storage/general/';

    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . 'enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getEndpoint(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . 'endpoint',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBucket(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . 'bucket',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getRegion(): string
    {
        return (string) ($this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . 'region',
            ScopeInterface::SCOPE_STORE
        ) ?: 'auto');
    }

    public function getAccessKey(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . 'access_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getSecretKey(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . 'secret_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCustomDomain(): string
    {
        return trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . 'custom_domain',
            ScopeInterface::SCOPE_STORE
        ));
    }

    public function usePathStyle(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . 'path_style',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Build the remote_storage config array for env.php
     */
    public function getRemoteStorageConfig(): array
    {
        return [
            'driver' => 'aws-s3',
            'config' => [
                'bucket' => $this->getBucket(),
                'region' => $this->getRegion(),
                'endpoint' => $this->getEndpoint(),
                'credentials' => [
                    'key' => $this->getAccessKey(),
                    'secret' => $this->getSecretKey(),
                ],
                'path_style' => $this->usePathStyle(),
            ],
        ];
    }
}
