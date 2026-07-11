<?php

declare(strict_types=1);

namespace WTD\Marketplace;

use WTD\Application\Application;

/**
 * Discovers local WTD package manifests.
 */
final class MarketplaceRegistry
{
    public function __construct(private readonly Application $app)
    {
    }

    /**
     * @return list<PackageManifest>
     */
    public function all(): array
    {
        $manifests = [];

        foreach ($this->manifestFiles() as $file) {
            $data = require $file;

            if (is_array($data)) {
                /** @var array<string, mixed> $data */
                $manifests[] = PackageManifest::fromArray($data);
            }
        }

        usort($manifests, static fn (PackageManifest $a, PackageManifest $b): int => $a->name <=> $b->name);

        return $manifests;
    }

    public function find(string $name): ?PackageManifest
    {
        foreach ($this->all() as $manifest) {
            if ($manifest->name === $name) {
                return $manifest;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function manifestFiles(): array
    {
        $directory = $this->app->basePath($this->packagesPath());
        $files = glob($directory . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'wtd-package.php');

        return is_array($files) ? array_values($files) : [];
    }

    private function packagesPath(): string
    {
        $path = $this->app->config()->get('marketplace.paths.packages', 'packages');

        return is_scalar($path) ? (string) $path : 'packages';
    }
}
