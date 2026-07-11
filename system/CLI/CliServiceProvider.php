<?php

declare(strict_types=1);

namespace WTD\CLI;

use WTD\Support\ServiceProvider;

final class CliServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(CliApplication::class);
    }
}
