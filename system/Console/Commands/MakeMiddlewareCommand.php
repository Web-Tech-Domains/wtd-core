<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class MakeMiddlewareCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'make:middleware';
    }

    public function description(): string
    {
        return 'Generate an application middleware.';
    }

    public function handle(Input $input, Output $output): int
    {
        $class = $this->className((string) $input->argument(0, 'Middleware'));
        $path = $input->option('path', 'app/Http/Middleware/' . $class . '.php');
        $path = $this->app->basePath(is_string($path) ? $path : 'app/Http/Middleware/' . $class . '.php');

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;

final class {$class} implements Middleware
{
    public function handle(Request \$request, Closure \$next): Response
    {
        return \$next(\$request);
    }
}

PHP);

        $output->line('Middleware created: ' . $path);

        return 0;
    }

    private function className(string $name): string
    {
        $base = preg_replace('/[^A-Za-z0-9_]+/', '', basename(str_replace('\\', '/', $name))) ?? '';

        if ($base === '') {
            return 'Middleware';
        }

        $base = str_replace('_', ' ', $base);
        $base = str_replace(' ', '', ucwords($base));

        return ctype_digit($base[0]) ? 'Middleware' . $base : $base;
    }
}
