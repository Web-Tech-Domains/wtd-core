<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class MakeControllerCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'make:controller';
    }

    public function description(): string
    {
        return 'Generate an application controller.';
    }

    public function handle(Input $input, Output $output): int
    {
        $name = $input->argument(0, 'HomeController');
        $class = $this->className((string) $name);
        $path = $this->app->basePath($this->path($input, 'app/Http/Controllers/' . $class . '.php'));

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use WTD\Http\Response;

final class {$class}
{
    public function __invoke(): Response
    {
        return Response::make('{$class}');
    }
}

PHP);

        $output->line('Controller created: ' . $path);

        return 0;
    }

    private function path(Input $input, string $default): string
    {
        $path = $input->option('path', $default);

        return is_string($path) ? $path : $default;
    }

    private function className(string $name): string
    {
        $base = basename(str_replace('\\', '/', $name));

        return $base === '' ? 'HomeController' : $base;
    }
}
