<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class MakeModelCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'make:model';
    }

    public function description(): string
    {
        return 'Generate an application model.';
    }

    public function handle(Input $input, Output $output): int
    {
        $name = $input->argument(0, 'Model');
        $class = $this->className((string) $name);
        $path = $this->app->basePath($this->path($input, 'app/Models/' . $class . '.php'));

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

namespace App\Models;

use WTD\ORM\Model;

final class {$class} extends Model
{
}

PHP);

        $output->line('Model created: ' . $path);

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

        return $base === '' ? 'Model' : $base;
    }
}
