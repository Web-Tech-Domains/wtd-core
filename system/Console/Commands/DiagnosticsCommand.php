<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Diagnostics;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

/**
 * Prints a JSON diagnostics report.
 */
final class DiagnosticsCommand implements Command
{
    public function __construct(private readonly Diagnostics $diagnostics)
    {
    }

    public function name(): string
    {
        return 'diagnostics';
    }

    public function description(): string
    {
        return 'Print runtime diagnostics as JSON.';
    }

    public function handle(Input $input, Output $output): int
    {
        $output->json($this->diagnostics->report());

        return 0;
    }
}
