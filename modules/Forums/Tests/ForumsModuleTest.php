<?php

declare(strict_types=1);

namespace Modules\Forums\Tests;

use PHPUnit\Framework\TestCase;

final class ForumsModuleTest extends TestCase
{
    public function testModuleMetadataIsValid(): void
    {
        $manifest = require dirname(__DIR__) . '/module.php';

        self::assertSame('Forums', $manifest['name']);
        self::assertSame('forums', $manifest['slug']);
        self::assertSame('modules/Forums/Routes/web.php', $manifest['routes']);
    }

    public function testForumViewUsesVueMountAndViteEntry(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__) . '/Resources/views/pages/index.php');

        self::assertStringContainsString('data-forums-app', $view);
        self::assertStringContainsString('forums-initial-state', $view);
        self::assertStringContainsString('{!! assetTags !!}', $view);
    }
}
