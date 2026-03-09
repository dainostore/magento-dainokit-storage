<?php

declare(strict_types=1);

namespace DainoKit\Storage\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\DeploymentConfig;
use Magento\RemoteStorage\Model\Config as RemoteStorageConfig;

class RemoteStorageConfigPlugin
{
    private ?array $configCache = null;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly DeploymentConfig $deploymentConfig
    ) {}

    public function afterGetDriver(RemoteStorageConfig $subject, $result): string
    {
        $config = $this->getStorageConfig();
        if ($config['enabled'] && $config['bucket']) {
            return 'aws-s3';
        }
        return (string) $result;
    }

    public function afterGetConfig(RemoteStorageConfig $subject, $result): array
    {
        $config = $this->getStorageConfig();
        if (!$config['enabled'] || !$config['bucket']) {
            return $result;
        }

        return [
            'bucket' => $config['bucket'],
            'region' => $config['region'] ?: 'auto',
            'endpoint' => [
                'url' => $config['endpoint'],
            ],
            'credentials' => [
                'key' => $config['access_key'],
                'secret' => $config['secret_key'],
            ],
            'path_style' => (bool) $config['path_style'],
        ];
    }

    public function afterGetPrefix(RemoteStorageConfig $subject, $result): string
    {
        $config = $this->getStorageConfig();
        if ($config['enabled'] && $config['bucket']) {
            return '';
        }
        return (string) $result;
    }

    /**
     * Read config directly from DB to avoid circular dependency with scopeConfig
     */
    private function getStorageConfig(): array
    {
        if ($this->configCache !== null) {
            return $this->configCache;
        }

        $defaults = [
            'enabled' => false,
            'endpoint' => 'https://s3.dainokit.com',
            'bucket' => '',
            'region' => 'auto',
            'access_key' => '',
            'secret_key' => '',
            'path_style' => true,
            'custom_domain' => '',
        ];

        try {
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName('core_config_data');
            $rows = $connection->fetchAll(
                $connection->select()
                    ->from($table, ['path', 'value'])
                    ->where('path LIKE ?', 'dainokit_storage/general/%')
                    ->where('scope = ?', 'default')
                    ->where('scope_id = ?', 0)
            );

            foreach ($rows as $row) {
                $key = str_replace('dainokit_storage/general/', '', $row['path']);
                if (array_key_exists($key, $defaults)) {
                    $defaults[$key] = $row['value'];
                }
            }

            // Decrypt secret key if encrypted
            if ($defaults['secret_key'] && str_contains((string) $defaults['secret_key'], ':')) {
                try {
                    $crypt = $this->deploymentConfig->get('crypt/key');
                    if ($crypt) {
                        $defaults['secret_key'] = $this->decryptValue((string) $defaults['secret_key'], $crypt);
                    }
                } catch (\Exception $e) {
                    // Use raw value as fallback
                }
            }

            $defaults['enabled'] = (bool) $defaults['enabled'];
            $defaults['path_style'] = (bool) $defaults['path_style'];
        } catch (\Exception $e) {
            // DB not available yet (during install), return defaults
        }

        $this->configCache = $defaults;
        return $this->configCache;
    }

    /**
     * Decrypt Magento encrypted config value
     */
    private function decryptValue(string $value, string $key): string
    {
        // Magento encrypted values are in format: <version>:<key_hash>:<iv>:<ciphertext>
        // or simply base64 encoded with the key
        // Use Magento's standard decryption approach
        $parts = explode(':', $value, 4);
        if (count($parts) === 4) {
            // Format: version:key_number:iv:ciphertext
            [, , $iv, $ciphertext] = $parts;
            $ivDecoded = base64_decode($iv);
            $ciphertextDecoded = base64_decode($ciphertext);
            $keys = explode("\n", $key);
            $cryptKey = end($keys);
            $cryptKey = substr(str_pad($cryptKey, 32, "\0"), 0, 32);
            $decrypted = openssl_decrypt(
                $ciphertextDecoded,
                'aes-256-cbc',
                $cryptKey,
                OPENSSL_RAW_DATA,
                $ivDecoded
            );
            return $decrypted !== false ? $decrypted : $value;
        }

        return $value;
    }
}
