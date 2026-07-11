<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class MakeCommandCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'make:command';
    }

    public function description(): string
    {
        return 'Generate an application console command.';
    }

    public function handle(Input $input, Output $output): int
    {
        $name = $input->argument(0, 'ExampleCommand');
        $class = $this->className((string) $name);
        $command = strtolower((string) $input->option('command', 'app:example'));
        $path = $this->app->basePath($this->path($input, 'app/Console/Commands/' . $class . '.php'));

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

final class {$class} implements Command
{
    public function name(): string
    {
        return '{$command}';
    }

    public function description(): string
    {
        return 'Application command.';
    }

    public function handle(Input \$input, Output \$output): int
    {
        \$output->line('{$command}');

        return 0;
    }
}

PHP);

        $output->line('Command created: ' . $path);

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

        return $base === '' ? 'ExampleCommand' : $base;
    }
}
