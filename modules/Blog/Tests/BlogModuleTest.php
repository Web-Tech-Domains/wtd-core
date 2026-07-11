<?php

declare(strict_types=1);

namespace Modules\Blog\Tests;

use PHPUnit\Framework\TestCase;

final class BlogModuleTest extends TestCase
{
    public function testModuleMetadataIsValid(): void
    {
        $manifest = require dirname(__DIR__) . '/module.php';

        self::assertSame('Blog', $manifest['name']);
        self::assertSame('blog', $manifest['slug']);
    }
}
