<?php

declare(strict_types=1);

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => 'storage/app',
            'url' => env('APP_URL', 'http://localhost') . '/storage',
        ],
        's3' => [
            'driver' => 's3',
            'url' => env('AWS_URL', 'https://s3.example.test'),
            'bucket' => env('AWS_BUCKET', ''),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],
        'r2' => [
            'driver' => 'r2',
            'url' => env('R2_URL', 'https://r2.example.test'),
            'bucket' => env('R2_BUCKET', ''),
            'account_id' => env('R2_ACCOUNT_ID', ''),
        ],
        'azure' => [
            'driver' => 'azure',
            'url' => env('AZURE_STORAGE_URL', 'https://azure.example.test'),
            'container' => env('AZURE_STORAGE_CONTAINER', ''),
        ],
        'gcs' => [
            'driver' => 'gcs',
            'url' => env('GCS_URL', 'https://gcs.example.test'),
            'bucket' => env('GCS_BUCKET', ''),
        ],
        'ftp' => [
            'driver' => 'ftp',
            'url' => env('FTP_URL', 'ftp://files.example.test'),
            'host' => env('FTP_HOST', ''),
            'username' => env('FTP_USERNAME', ''),
        ],
        'sftp' => [
            'driver' => 'sftp',
            'url' => env('SFTP_URL', 'sftp://files.example.test'),
            'host' => env('SFTP_HOST', ''),
            'username' => env('SFTP_USERNAME', ''),
        ],
    ],
];
