<?php

declare(strict_types=1);

namespace WTD\AI;

use WTD\Console\Commands\AiProvidersCommand;
use WTD\Console\Kernel;
use WTD\Support\ServiceProvider;

final class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(AiManager::class);
    }

    public function boot(): void
    {
        if (!$this->container()->has(Kernel::class)) {
            return;
        }

        /** @var Kernel $kernel */
        $kernel = $this->container()->get(Kernel::class);
        $kernel->register($this->container()->get(AiProvidersCommand::class));
    }
}
