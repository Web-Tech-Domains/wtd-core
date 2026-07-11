<?php

declare(strict_types=1);

namespace WTD\Storage;

use InvalidArgumentException;
use WTD\Filesystem\Filesystem;

final class StorageManager
{
    /**
     * @var array<string, StorageDisk>
     */
    private array $disks = [];

    /**
     * @param array<string, string> $urls
     */
    public function __construct(
        private readonly string $default = 'local',
        string $localRoot = '',
        string $localUrl = '/storage',
        string $secret = 'wtd-core',
        array $urls = [],
    ) {
        $signer = new SignedUrlGenerator($secret);
        $this->disks = [
            'local' => new LocalDisk(new Filesystem(), $localRoot === '' ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wtd-storage' : $localRoot, $localUrl, $signer),
            's3' => new S3Disk($this->url($urls, 's3', 'https://s3.example.test'), $signer),
            'r2' => new R2Disk($this->url($urls, 'r2', 'https://r2.example.test'), $signer),
            'azure' => new AzureDisk($this->url($urls, 'azure', 'https://azure.example.test'), $signer),
            'gcs' => new GoogleCloudDisk($this->url($urls, 'gcs', 'https://gcs.example.test'), $signer),
            'ftp' => new FtpDisk($this->url($urls, 'ftp', 'ftp://files.example.test'), $signer),
            'sftp' => new SftpDisk($this->url($urls, 'sftp', 'sftp://files.example.test'), $signer),
        ];
    }

    public function disk(?string $name = null): StorageDisk
    {
        $name ??= $this->default;

        return $this->disks[$name] ?? throw new InvalidArgumentException(sprintf('Storage disk [%s] is not configured.', $name));
    }

    public function extend(string $name, StorageDisk $disk): void
    {
        $this->disks[$name] = $disk;
    }

    /**
     * @param array<string, string> $urls
     */
    private function url(array $urls, string $disk, string $default): string
    {
        return $urls[$disk] ?? $default;
    }
}
