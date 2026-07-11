<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use RuntimeException;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Marketplace\PackageInstaller;

final class MarketplaceInstallCommand implements Command
{
    public function __construct(private readonly PackageInstaller $installer)
    {
    }

    public function name(): string
    {
        return 'marketplace:install';
    }

    public function description(): string
    {
        return 'Install a local marketplace package.';
    }

    public function handle(Input $input, Output $output): int
    {
        $name = $input->argument(0);

        if ($name === null) {
            $output->error('Package name is required.');
            return 1;
        }

        try {
            $manifest = $this->installer->install($name);
        } catch (RuntimeException $exception) {
            $output->error($exception->getMessage());
            return 1;
        }

        $output->line('Package installed: ' . $manifest->name);

        return 0;
    }
}
