<?php

declare(strict_types=1);

namespace DainoKit\Storage\Model;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class ConnectionTest
{
    /**
     * Test S3 connection with the given credentials
     */
    public function execute(
        string $endpoint,
        string $bucket,
        string $region,
        string $accessKey,
        string $secretKey,
        bool $pathStyle = true
    ): array {
        try {
            $client = new S3Client([
                'version' => 'latest',
                'region' => $region ?: 'auto',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => $pathStyle,
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
                'http' => [
                    'timeout' => 10,
                    'connect_timeout' => 5,
                ],
            ]);

            // Try listing objects (limit 1) to verify access
            $result = $client->listObjectsV2([
                'Bucket' => $bucket,
                'MaxKeys' => 1,
            ]);

            $count = $result['KeyCount'] ?? 0;

            return [
                'success' => true,
                'message' => sprintf(
                    'Connected to bucket "%s". %s object(s) found.',
                    $bucket,
                    $count > 0 ? $count . '+' : '0'
                ),
            ];
        } catch (AwsException $e) {
            $msg = $e->getAwsErrorMessage() ?: $e->getMessage();
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $msg,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
