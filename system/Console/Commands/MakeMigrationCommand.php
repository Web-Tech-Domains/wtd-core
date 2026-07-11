<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class MakeMigrationCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'make:migration';
    }

    public function description(): string
    {
        return 'Generate a database migration.';
    }

    public function handle(Input $input, Output $output): int
    {
        $name = $this->migrationName((string) $input->argument(0, 'create_table'));
        $table = $this->identifier($input->option('table', $this->tableFromName($name)), $this->tableFromName($name));
        $path = $input->option('path', 'database/migrations/' . date('Y_m_d_His') . '_' . $name . '.php');
        $path = $this->app->basePath(is_string($path) ? $path : 'database/migrations/' . date('Y_m_d_His') . '_' . $name . '.php');

        $this->files->put($path, <<<PHP
<?php

declare(strict_types=1);

use WTD\Database\Blueprint;
use WTD\Database\Migration;
use WTD\Database\Schema;

return new class implements Migration {
    public function up(Schema \$schema): void
    {
        \$schema->create('{$table}', static function (Blueprint \$table): void {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(Schema \$schema): void
    {
        \$schema->dropIfExists('{$table}');
    }
};

PHP);

        $output->line('Migration created: ' . $path);

        return 0;
    }

    private function migrationName(string $name): string
    {
        $name = strtolower(preg_replace('/[^A-Za-z0-9_]+/', '_', $name) ?? 'create_table');

        return trim($name, '_') === '' ? 'create_table' : trim($name, '_');
    }

    private function tableFromName(string $name): string
    {
        if (preg_match('/create_(.+)_table/', $name, $matches) === 1) {
            return $this->identifier($matches[1], 'records');
        }

        return 'records';
    }

    private function identifier(mixed $value, string $fallback): string
    {
        $identifier = is_string($value) ? strtolower($value) : $fallback;
        $identifier = preg_replace('/[^a-z0-9_]+/', '_', $identifier) ?? $fallback;
        $identifier = trim($identifier, '_');

        return $identifier === '' ? $fallback : $identifier;
    }
}
