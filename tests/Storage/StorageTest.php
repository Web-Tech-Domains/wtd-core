<?php

declare(strict_types=1);

namespace Tests\Storage;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Storage\AzureDisk;
use WTD\Storage\FtpDisk;
use WTD\Storage\GoogleCloudDisk;
use WTD\Storage\LocalDisk;
use WTD\Storage\R2Disk;
use WTD\Storage\S3Disk;
use WTD\Storage\SftpDisk;
use WTD\Storage\SignedUrlGenerator;
use WTD\Storage\StorageDisk;
use WTD\Storage\StorageManager;
use WTD\Storage\StorageServiceProvider;

final class StorageTest extends TestCase
{
    public function testLocalDiskStoresReadsUrlsAndDeletesFiles(): void
    {
        $manager = new StorageManager(localRoot: dirname(__DIR__) . '/tmp/storage', secret: 'secret');
        $disk = $manager->disk('local');
        $disk->put('docs/readme.txt', 'Hello');

        self::assertInstanceOf(LocalDisk::class, $disk);
        self::assertTrue($disk->exists('docs/readme.txt'));
        self::assertSame('Hello', $disk->get('docs/readme.txt'));
        self::assertSame('/storage/docs/readme.txt', $disk->url('docs/readme.txt'));

        $disk->delete('docs/readme.txt');

        self::assertFalse($disk->exists('docs/readme.txt'));
    }

    public function testCloudAndRemoteDiskFacadesShareStorageContract(): void
    {
        $manager = new StorageManager(secret: 'secret');

        self::assertInstanceOf(S3Disk::class, $manager->disk('s3'));
        self::assertInstanceOf(R2Disk::class, $manager->disk('r2'));
        self::assertInstanceOf(AzureDisk::class, $manager->disk('azure'));
        self::assertInstanceOf(GoogleCloudDisk::class, $manager->disk('gcs'));
        self::assertInstanceOf(FtpDisk::class, $manager->disk('ftp'));
        self::assertInstanceOf(SftpDisk::class, $manager->disk('sftp'));

        $manager->disk('s3')->put('avatar.png', 'image');

        self::assertSame('image', $manager->disk('s3')->get('avatar.png'));
        self::assertSame('https://s3.example.test/avatar.png', $manager->disk('s3')->url('avatar.png'));
    }

    public function testTemporaryUrlsAreSignedAndValidated(): void
    {
        $manager = new StorageManager(secret: 'secret');
        $url = $manager->disk('s3')->temporaryUrl('report.pdf', time() + 60);
        $signer = new SignedUrlGenerator('secret');

        self::assertStringContainsString('signature=', $url);
        self::assertTrue($signer->validate($url));
    }

    public function testStorageServiceProviderRegistersManagerAndDefaultDisk(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application($basePath, new Container(), new Repository([
            'filesystems.default' => 's3',
            'filesystems.disks.s3.url' => 'https://cdn.example.test',
            'app.key' => 'secret',
        ]));
        $app->register(StorageServiceProvider::class);

        self::assertInstanceOf(StorageManager::class, $app->container()->get(StorageManager::class));
        self::assertInstanceOf(StorageDisk::class, $app->container()->get(StorageDisk::class));
        self::assertInstanceOf(S3Disk::class, $app->container()->get(StorageDisk::class));
        self::assertSame('https://cdn.example.test/docs/readme.txt', $app->container()->get(StorageDisk::class)->url('docs/readme.txt'));
    }
}
