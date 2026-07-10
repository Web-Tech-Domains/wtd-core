<?php

declare(strict_types=1);

namespace WTD\Application;

use InvalidArgumentException;
use WTD\Support\ServiceProvider;

/**
 * Registers configured service providers with the application.
 */
final class ProviderBootstrapper
{
    public function __construct(private readonly Application $app)
    {
    }

    /**
     * Register provider class names from configuration.
     *
     * @param list<class-string<ServiceProvider>> $providers
     */
    public function bootstrap(array $providers): void
    {
        foreach ($providers as $provider) {
            if (!is_subclass_of($provider, ServiceProvider::class)) {
                throw new InvalidArgumentException(sprintf(
                    'Configured provider [%s] must extend %s.',
                    $provider,
                    ServiceProvider::class,
                ));
            }

            $this->app->register($provider);
        }
    }
}
