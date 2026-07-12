<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use WTD\Console\ComposerAutoMigrator;

final class ComposerAutoMigratorTest extends TestCase
{
    public function testAutoMigrationCanBeDisabledFromDotEnv(): void
    {
        $basePath = dirname(__DIR__) . '/tmp/composer-auto-migrator-disabled';
        $this->deleteDirectory($basePath);
        mkdir($basePath, 0775, true);
        file_put_contents($basePath . '/.env', "WTD_AUTO_MIGRATE=false\n");

        $bufferLevel = ob_get_level();
        ob_start();

        try {
            $status = (new ComposerAutoMigrator($basePath))->run();
            $output = (string) ob_get_clean();

            self::assertSame(0, $status);
            self::assertStringContainsString('Auto migration skipped', $output);
        } finally {
            if (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            $this->deleteDirectory($basePath);
        }
    }

    public function testProcessEnvironmentOverridesDotEnv(): void
    {
        $basePath = dirname(__DIR__) . '/tmp/composer-auto-migrator-server-env';
        $this->deleteDirectory($basePath);
        mkdir($basePath, 0775, true);
        file_put_contents($basePath . '/.env', "WTD_AUTO_MIGRATE=true\n");

        $_SERVER['WTD_AUTO_MIGRATE'] = 'false';
        $bufferLevel = ob_get_level();
        ob_start();

        try {
            $status = (new ComposerAutoMigrator($basePath))->run();
            $output = (string) ob_get_clean();

            self::assertSame(0, $status);
            self::assertStringContainsString('Auto migration skipped', $output);
        } finally {
            unset($_SERVER['WTD_AUTO_MIGRATE']);

            if (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            $this->deleteDirectory($basePath);
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = scandir($path);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $file;

            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
                continue;
            }

            unlink($fullPath);
        }

        rmdir($path);
    }
}
