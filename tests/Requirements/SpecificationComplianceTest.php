<?php

declare(strict_types=1);

namespace Tests\Requirements;

use PHPUnit\Framework\TestCase;

final class SpecificationComplianceTest extends TestCase
{
    public function testOperationalToolingFilesExist(): void
    {
        foreach ([
            'Dockerfile',
            'docker-compose.yml',
            'public/.htaccess',
            'docker/nginx/default.conf',
            'docker/apache/vhost.conf',
            'docker/php-fpm/wtd.ini',
            '.github/workflows/quality-gate.yml',
            'rector.php',
            'package.json',
            'vite.config.js',
            'resources/js/app.js',
            'resources/js/vue.js',
            'resources/js/react.jsx',
            'resources/css/app.css',
            'docs/REQUIREMENTS_MATRIX.md',
        ] as $path) {
            self::assertFileExists($this->root($path));
        }
    }

    public function testEnterpriseModulesExist(): void
    {
        foreach ([
            'system/Marketplace/MarketplaceServiceProvider.php',
            'system/Tenancy/TenancyServiceProvider.php',
            'system/AI/AIServiceProvider.php',
            'system/Monitoring/MonitoringServiceProvider.php',
        ] as $path) {
            self::assertFileExists($this->root($path));
        }
    }

    public function testScaffoldedFrameworkFoldersContainRealModules(): void
    {
        foreach ([
            'system/CLI/CliApplication.php',
            'system/View/ViewRenderer.php',
            'system/WebSocket/WebSocketHandshake.php',
        ] as $path) {
            self::assertFileExists($this->root($path));
        }
    }

    private function root(string $path): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
