<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\DeveloperExperience\OpenApiGenerator;
use WTD\Filesystem\Filesystem;

final class ApiDocsCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
        private readonly OpenApiGenerator $generator,
    ) {
    }

    public function name(): string
    {
        return 'api:docs';
    }

    public function description(): string
    {
        return 'Generate OpenAPI documentation.';
    }

    public function handle(Input $input, Output $output): int
    {
        $path = $input->option('path', 'storage/api/openapi.json');
        $path = $this->app->basePath(is_string($path) ? $path : 'storage/api/openapi.json');
        $this->files->put($path, json_encode($this->generator->generate(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL);
        $output->line('OpenAPI documentation written: ' . $path);

        return 0;
    }
}
