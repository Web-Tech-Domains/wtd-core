<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class ProjectNewCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'app:new';
    }

    public function description(): string
    {
        return 'Create a minimal WTD Core project skeleton.';
    }

    public function handle(Input $input, Output $output): int
    {
        $name = $input->argument(0, 'wtd-app');
        $path = $this->app->basePath($this->path($input, 'storage/app/projects/' . $name));

        $this->files->put($path . DIRECTORY_SEPARATOR . 'README.md', '# ' . $name . PHP_EOL);
        $this->files->put($path . DIRECTORY_SEPARATOR . 'routes/web.php', "<?php\n\ndeclare(strict_types=1);\n");
        $this->files->put($path . DIRECTORY_SEPARATOR . 'config/app.php', "<?php\n\nreturn ['name' => '{$name}'];\n");

        $output->line('Project created: ' . $path);

        return 0;
    }

    private function path(Input $input, string $default): string
    {
        $path = $input->option('path', $default);

        return is_string($path) ? $path : $default;
    }
}
