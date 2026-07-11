<?php

declare(strict_types=1);

namespace WTD\Marketplace;

use WTD\Console\Commands\MarketplaceInstallCommand;
use WTD\Console\Commands\MarketplaceListCommand;
use WTD\Console\Commands\MarketplacePublishCommand;
use WTD\Console\Kernel;
use WTD\Support\ServiceProvider;

/**
 * Registers local package marketplace services.
 */
final class MarketplaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(MarketplaceRegistry::class);
        $this->container()->singleton(PackageInstaller::class);
    }

    public function boot(): void
    {
        if ($this->container()->has(Kernel::class)) {
            /** @var Kernel $kernel */
            $kernel = $this->container()->get(Kernel::class);
            $kernel->register($this->container()->get(MarketplaceListCommand::class));
            $kernel->register($this->container()->get(MarketplaceInstallCommand::class));
            $kernel->register($this->container()->get(MarketplacePublishCommand::class));
        }

        if (!(bool) $this->app->config()->get('marketplace.auto_register', true)) {
            return;
        }

        /** @var PackageInstaller $installer */
        $installer = $this->container()->get(PackageInstaller::class);

        foreach ($installer->installedProviders() as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }
}
