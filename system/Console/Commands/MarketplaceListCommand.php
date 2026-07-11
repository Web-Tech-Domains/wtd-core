<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Marketplace\MarketplaceRegistry;
use WTD\Marketplace\PackageInstaller;

final class MarketplaceListCommand implements Command
{
    public function __construct(
        private readonly MarketplaceRegistry $registry,
        private readonly PackageInstaller $installer,
    ) {
    }

    public function name(): string
    {
        return 'marketplace:list';
    }

    public function description(): string
    {
        return 'List local marketplace packages.';
    }

    public function handle(Input $input, Output $output): int
    {
        $installed = array_keys($this->installer->installed());

        foreach ($this->registry->all() as $package) {
            $status = in_array($package->name, $installed, true) ? 'installed' : 'available';
            $output->line($package->name . ' ' . $package->version . ' [' . $status . '] - ' . $package->description);
        }

        if ($this->registry->all() === []) {
            $output->line('No local marketplace packages found.');
        }

        return 0;
    }
}
