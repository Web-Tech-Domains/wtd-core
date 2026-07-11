<?php

declare(strict_types=1);

namespace WTD\Marketplace;

use RuntimeException;
use WTD\Application\Application;
use WTD\Filesystem\Filesystem;

/**
 * Installs local marketplace packages into framework metadata.
 */
final class PackageInstaller
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
        private readonly MarketplaceRegistry $registry,
    ) {
    }

    public function install(string $name): PackageManifest
    {
        $manifest = $this->registry->find($name);

        if (!$manifest instanceof PackageManifest) {
            throw new RuntimeException(sprintf('Package [%s] was not found.', $name));
        }

        $installed = $this->installed();
        $installed[$manifest->name] = $manifest->toArray();
        $this->writeInstalled($installed);

        return $manifest;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function installed(): array
    {
        $path = $this->installedPath();

        if (!is_file($path)) {
            return [];
        }

        $data = require $path;

        return is_array($data) ? $this->normalizeInstalled($data) : [];
    }

    /**
     * @return list<string>
     */
    public function installedProviders(): array
    {
        $providers = [];

        foreach ($this->installed() as $package) {
            foreach (($package['providers'] ?? []) as $provider) {
                if (is_string($provider)) {
                    $providers[] = $provider;
                }
            }
        }

        return array_values(array_unique($providers));
    }

    /**
     * Publish package config files and return published destinations.
     *
     * @return list<string>
     */
    public function publish(string $name): array
    {
        $manifest = $this->registry->find($name);

        if (!$manifest instanceof PackageManifest) {
            throw new RuntimeException(sprintf('Package [%s] was not found.', $name));
        }

        $published = [];

        foreach ($manifest->config as $source => $target) {
            $sourcePath = $this->app->basePath($this->packagesPath() . DIRECTORY_SEPARATOR . $source);
            $targetPath = $this->app->basePath($target);

            if (!is_file($sourcePath)) {
                continue;
            }

            $this->files->put($targetPath, $this->files->get($sourcePath));
            $published[] = $targetPath;
        }

        return $published;
    }

    private function installedPath(): string
    {
        $path = $this->app->config()->get('marketplace.paths.installed', 'storage/framework/marketplace.php');

        return $this->app->basePath(is_scalar($path) ? (string) $path : 'storage/framework/marketplace.php');
    }

    private function packagesPath(): string
    {
        $path = $this->app->config()->get('marketplace.paths.packages', 'packages');

        return is_scalar($path) ? (string) $path : 'packages';
    }

    /**
     * @param array<string, array<string, mixed>> $installed
     */
    private function writeInstalled(array $installed): void
    {
        ksort($installed);
        $this->files->put(
            $this->installedPath(),
            "<?php\n\nreturn " . var_export($installed, true) . ";\n",
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<string, array<string, mixed>>
     */
    private function normalizeInstalled(array $data): array
    {
        $installed = [];

        foreach ($data as $name => $package) {
            if (is_string($name) && is_array($package)) {
                /** @var array<string, mixed> $package */
                $installed[$name] = $package;
            }
        }

        return $installed;
    }
}
