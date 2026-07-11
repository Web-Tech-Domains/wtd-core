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

    public function __construct(
        private readonly string $default = 'local',
        string $localRoot = '',
        string $secret = 'wtd-core',
    ) {
        $signer = new SignedUrlGenerator($secret);
        $this->disks = [
            'local' => new LocalDisk(new Filesystem(), $localRoot === '' ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wtd-storage' : $localRoot, signer: $signer),
            's3' => new S3Disk('https://s3.example.test', $signer),
            'r2' => new R2Disk('https://r2.example.test', $signer),
            'azure' => new AzureDisk('https://azure.example.test', $signer),
            'gcs' => new GoogleCloudDisk('https://gcs.example.test', $signer),
            'ftp' => new FtpDisk('ftp://files.example.test', $signer),
            'sftp' => new SftpDisk('sftp://files.example.test', $signer),
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
}
