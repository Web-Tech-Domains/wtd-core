<?php

declare(strict_types=1);

namespace WTD\Console;

use WTD\Console\Commands\AboutCommand;
use WTD\Console\Commands\CacheClearCommand;
use WTD\Console\Commands\ConfigCacheCommand;
use WTD\Console\Commands\ConfigClearCommand;
use WTD\Console\Commands\DeployCommand;
use WTD\Console\Commands\DiagnosticsCommand;
use WTD\Console\Commands\DownCommand;
use WTD\Console\Commands\EnvironmentCommand;
use WTD\Console\Commands\HealthCommand;
use WTD\Console\Commands\HelpCommand;
use WTD\Console\Commands\ListCommand;
use WTD\Console\Commands\MakeCommandCommand;
use WTD\Console\Commands\MakeControllerCommand;
use WTD\Console\Commands\MakeMiddlewareCommand;
use WTD\Console\Commands\MakeMigrationCommand;
use WTD\Console\Commands\MakeModelCommand;
use WTD\Console\Commands\MakeModuleCommand;
use WTD\Console\Commands\MakeSeederCommand;
use WTD\Console\Commands\MigrateCommand;
use WTD\Console\Commands\MigrateRollbackCommand;
use WTD\Console\Commands\OptimizeClearCommand;
use WTD\Console\Commands\OptimizeCommand;
use WTD\Console\Commands\ProjectNewCommand;
use WTD\Console\Commands\QueueWorkCommand;
use WTD\Console\Commands\RouteCacheCommand;
use WTD\Console\Commands\RouteClearCommand;
use WTD\Console\Commands\ScheduleRunCommand;
use WTD\Console\Commands\SeedCommand;
use WTD\Console\Commands\ServeCommand;
use WTD\Console\Commands\TestCommand;
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
            CacheClearCommand::class,
            ConfigCacheCommand::class,
            ConfigClearCommand::class,
            DeployCommand::class,
            DiagnosticsCommand::class,
            DownCommand::class,
            EnvironmentCommand::class,
            HealthCommand::class,
            MakeCommandCommand::class,
            MakeControllerCommand::class,
            MakeMiddlewareCommand::class,
            MakeMigrationCommand::class,
            MakeModelCommand::class,
            MakeModuleCommand::class,
            MakeSeederCommand::class,
            MigrateCommand::class,
            MigrateRollbackCommand::class,
            OptimizeCommand::class,
            OptimizeClearCommand::class,
            ProjectNewCommand::class,
            QueueWorkCommand::class,
            RouteCacheCommand::class,
            RouteClearCommand::class,
            ScheduleRunCommand::class,
            SeedCommand::class,
            ServeCommand::class,
            TestCommand::class,
            UpCommand::class,
        ];
    }
}
