<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Tenancy\TenantManager;

final class TenantListCommand implements Command
{
    public function __construct(private readonly TenantManager $tenants)
    {
    }

    public function name(): string
    {
        return 'tenant:list';
    }

    public function description(): string
    {
        return 'List configured tenants.';
    }

    public function handle(Input $input, Output $output): int
    {
        foreach ($this->tenants->all() as $tenant) {
            $output->line($tenant->id . ' - ' . $tenant->name);
        }

        if ($this->tenants->all() === []) {
            $output->line('No tenants configured.');
        }

        return 0;
    }
}
