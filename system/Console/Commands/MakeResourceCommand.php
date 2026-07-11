<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\DeveloperExperience\CodeGenerator;
use WTD\Filesystem\Filesystem;

final class MakeResourceCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
        private readonly CodeGenerator $generator,
    ) {
    }

    public function name(): string
    {
        return 'make:resource';
    }

    public function description(): string
    {
        return 'Generate an API resource controller and route snippet.';
    }

    public function handle(Input $input, Output $output): int
    {
        $name = $this->className((string) $input->argument(0, 'Resource'));
        $model = $this->className((string) $input->option('model', $name));
        $controller = $name . 'Controller';
        $uri = '/' . strtolower($name);
        $path = $input->option('path', 'app/Http/Controllers/' . $controller . '.php');
        $path = $this->app->basePath(is_string($path) ? $path : 'app/Http/Controllers/' . $controller . '.php');

        $this->files->put($path, $this->generator->apiResourceController($controller, $model));
        $output->line('Resource controller created: ' . $path);
        $output->line('Route snippet:');
        $output->line($this->generator->apiResourceRoutes($uri, $controller));

        return 0;
    }

    private function className(string $name): string
    {
        $base = basename(str_replace('\\', '/', $name));

        return $base === '' ? 'Resource' : $base;
    }
}
