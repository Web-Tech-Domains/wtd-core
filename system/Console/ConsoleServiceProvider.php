<?php

declare(strict_types=1);

namespace WTD\Console;

use WTD\Console\Commands\AboutCommand;
use WTD\Console\Commands\ConfigCacheCommand;
use WTD\Console\Commands\ConfigClearCommand;
use WTD\Console\Commands\DiagnosticsCommand;
use WTD\Console\Commands\DownCommand;
use WTD\Console\Commands\EnvironmentCommand;
use WTD\Console\Commands\HealthCommand;
use WTD\Console\Commands\HelpCommand;
use WTD\Console\Commands\ListCommand;
use WTD\Console\Commands\OptimizeClearCommand;
use WTD\Console\Commands\OptimizeCommand;
use WTD\Console\Commands\UpCommand;
use WTD\Support\ServiceProvider;

/**
 * Registers built-in console services and commands.
 */
final class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register the console kernel and built-in commands.
     */
    public function register(): void
    {
        $this->container()->singleton(Kernel::class, function (): Kernel {
            $kernel = new Kernel();

            foreach ($this->commands() as $command) {
                $kernel->register($this->container()->get($command));
            }

            $kernel->register(new ListCommand($kernel));
            $kernel->register(new HelpCommand($kernel));

            return $kernel;
        });
    }

    /**
     * Return built-in console command classes.
     *
     * @return list<class-string<Command>>
     */
    private function commands(): array
    {
        return [
            AboutCommand::class,
            ConfigCacheCommand::class,
            ConfigClearCommand::class,
            DiagnosticsCommand::class,
            DownCommand::class,
            EnvironmentCommand::class,
            HealthCommand::class,
            OptimizeCommand::class,
            OptimizeClearCommand::class,
            UpCommand::class,
        ];
    }
}
