<?php

declare(strict_types=1);

namespace WTD\Tenancy;

use WTD\Console\Commands\TenantListCommand;
use WTD\Console\Kernel;
use WTD\Support\ServiceProvider;

final class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(TenantManager::class);
        $this->container()->singleton(TenantResolver::class);
    }

    public function boot(): void
    {
        if (!$this->container()->has(Kernel::class)) {
            return;
        }

        /** @var Kernel $kernel */
        $kernel = $this->container()->get(Kernel::class);
        $kernel->register($this->container()->get(TenantListCommand::class));
    }
}
