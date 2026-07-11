<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use RuntimeException;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Marketplace\PackageInstaller;

final class MarketplacePublishCommand implements Command
{
    public function __construct(private readonly PackageInstaller $installer)
    {
    }

    public function name(): string
    {
        return 'marketplace:publish';
    }

    public function description(): string
    {
        return 'Publish package configuration files.';
    }

    public function handle(Input $input, Output $output): int
    {
        $name = $input->argument(0);

        if ($name === null) {
            $output->error('Package name is required.');
            return 1;
        }

        try {
            $published = $this->installer->publish($name);
        } catch (RuntimeException $exception) {
            $output->error($exception->getMessage());
            return 1;
        }

        foreach ($published as $path) {
            $output->line('Published: ' . $path);
        }

        if ($published === []) {
            $output->line('No publishable files found.');
        }

        return 0;
    }
}
