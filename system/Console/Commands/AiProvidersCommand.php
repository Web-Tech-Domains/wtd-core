<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\AI\AiManager;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

final class AiProvidersCommand implements Command
{
    public function __construct(private readonly AiManager $ai)
    {
    }

    public function name(): string
    {
        return 'ai:providers';
    }

    public function description(): string
    {
        return 'List configured AI package providers.';
    }

    public function handle(Input $input, Output $output): int
    {
        foreach ($this->ai->providers() as $provider) {
            $output->line($provider);
        }

        return 0;
    }
}
