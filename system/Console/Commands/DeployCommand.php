<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

final class DeployCommand implements Command
{
    public function __construct(private readonly Application $app)
    {
    }

    public function name(): string
    {
        return 'deploy';
    }

    public function description(): string
    {
        return 'Run deployment readiness checks.';
    }

    public function handle(Input $input, Output $output): int
    {
        $checks = [
            'base_path' => is_dir($this->app->basePath()),
            'public_index' => is_file($this->app->basePath('public/index.php')),
            'storage' => is_dir($this->app->basePath('storage')),
            'environment' => $this->app->config()->get('app.env', 'production'),
        ];

        $output->json(['deployable' => !in_array(false, $checks, true), 'checks' => $checks]);

        return 0;
    }
}
