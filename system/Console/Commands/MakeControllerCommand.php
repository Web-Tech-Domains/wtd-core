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
        $class = $this->controllerClass((string) $name);
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

    private function controllerClass(string $name): string
    {
        $base = preg_replace('/[^A-Za-z0-9_]+/', '', basename(str_replace('\\', '/', $name))) ?? '';

        if ($base === '') {
            return 'HomeController';
        }

        if (!str_ends_with(strtolower($base), 'controller')) {
            if (str_ends_with(strtolower($base), 'ies')) {
                $base = substr($base, 0, -3) . 'y';
            } elseif (str_ends_with(strtolower($base), 's') && !str_ends_with(strtolower($base), 'ss')) {
                $base = substr($base, 0, -1);
            }

            $base .= 'Controller';
        }

        $base = str_replace('_', ' ', $base);
        $base = str_replace(' ', '', ucwords($base));

        return ctype_digit($base[0]) ? 'Controller' . $base : $base;
    }
}
