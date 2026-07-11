<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class MakeSeederCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'make:seeder';
    }

    public function description(): string
    {
        return 'Generate a database seeder.';
    }

    public function handle(Input $input, Output $output): int
    {
        $class = $this->className((string) $input->argument(0, 'DatabaseSeeder'));
        $path = $input->option('path', 'database/seeders/' . $class . '.php');
        $path = $this->app->basePath(is_string($path) ? $path : 'database/seeders/' . $class . '.php');

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

use WTD\Database\Connection;
use WTD\Database\Seeder;

return new class implements Seeder {
    public function run(Connection \$connection): void
    {
        // Add seed data here.
    }
};

PHP);

        $output->line('Seeder created: ' . $path);

        return 0;
    }

    private function className(string $name): string
    {
        $base = preg_replace('/[^A-Za-z0-9_]+/', '', basename(str_replace('\\', '/', $name))) ?? '';

        if ($base === '') {
            return 'DatabaseSeeder';
        }

        return ctype_digit($base[0]) ? 'Seeder' . $base : $base;
    }
}
