<?php

declare(strict_types=1);

namespace Tests\Filesystem;

use PHPUnit\Framework\TestCase;
use WTD\Filesystem\Filesystem;

final class FilesystemTest extends TestCase
{
    public function testFilesystemWritesAndReadsFiles(): void
    {
        $filesystem = new Filesystem();
        $path = dirname(__DIR__) . '/tmp/filesystem/example.txt';

        $filesystem->put($path, 'example');

        self::assertTrue($filesystem->exists($path));
        self::assertSame('example', $filesystem->get($path));
    }
}
