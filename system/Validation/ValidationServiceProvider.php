<?php

declare(strict_types=1);

namespace WTD\Validation;

use WTD\Support\ServiceProvider;

/**
 * Registers validation services.
 */
final class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register validation service bindings.
     */
    public function register(): void
    {
        $this->container()->singleton(Validator::class);
    }
}
