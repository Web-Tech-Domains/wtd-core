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
        $resource = $this->resourceName((string) $name);
        $class = $this->className($resource);
        $tableOption = $input->option('table');
        $table = is_string($tableOption) ? $this->tableName($tableOption) : $this->pluralTable($resource);
        $path = $this->app->basePath($this->path($input, 'app/Models/' . $class . '.php'));

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

namespace App\Models;

use WTD\ORM\Model;

final class {$class} extends Model
{
    protected ?string \$table = '{$table}';

    protected bool \$useTimestamps = true;

    protected bool \$protectFields = true;

    /**
     * @var list<string>
     */
    protected array \$allowedFields = [];
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

    private function resourceName(string $name): string
    {
        $base = preg_replace('/[^A-Za-z0-9_]+/', '', basename(str_replace('\\', '/', $name))) ?? '';

        if ($base === '') {
            return 'Model';
        }

        if (str_ends_with(strtolower($base), 'ies')) {
            $base = substr($base, 0, -3) . 'y';
        } elseif (str_ends_with(strtolower($base), 's') && !str_ends_with(strtolower($base), 'ss')) {
            $base = substr($base, 0, -1);
        }

        return ctype_digit($base[0]) ? 'Model' . $base : $base;
    }

    private function className(string $name): string
    {
        $name = str_replace('_', ' ', $name);
        $name = str_replace(' ', '', ucwords($name));

        return $name === '' ? 'Model' : $name;
    }

    private function tableName(string $value): string
    {
        $table = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
        $table = preg_replace('/[^a-z0-9_]+/', '_', $table) ?? 'models';
        $table = trim($table, '_');

        return $table === '' ? 'models' : $table;
    }

    private function pluralTable(string $resource): string
    {
        $table = $this->tableName($resource);

        if (str_ends_with($table, 'y')) {
            return substr($table, 0, -1) . 'ies';
        }

        return str_ends_with($table, 's') ? $table : $table . 's';
    }
}
