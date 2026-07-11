<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Filesystem\Filesystem;

final class MakeModuleCommand implements Command
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function name(): string
    {
        return 'make:module';
    }

    public function description(): string
    {
        return 'Generate a complete application module structure.';
    }

    public function handle(Input $input, Output $output): int
    {
        $module = $this->className((string) $input->argument(0, 'Module'));
        $root = $input->option('path', 'modules/' . $module);
        $root = $this->app->basePath(is_string($root) ? $root : 'modules/' . $module);
        $slug = $this->slug($module);

        foreach ($this->files($module, $slug) as $path => $contents) {
            $this->files->put($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path), $contents);
        }

        $output->line('Module created: ' . $root);
        $output->line('Provider: Modules\\' . $module . '\\Providers\\' . $module . 'ServiceProvider');
        $output->line("Routes: modules/{$module}/Routes/web.php");

        return 0;
    }

    /**
     * @return array<string, string>
     */
    private function files(string $module, string $slug): array
    {
        $namespace = 'Modules\\' . $module;
        $table = str_replace('-', '_', $slug) . 's';

        return [
            'README.md' => "# {$module} Module\n\nGenerated WTD Core module.\n",
            'module.php' => $this->moduleManifest($module, $slug),
            'Config/config.php' => "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    'name' => '{$module}',\n];\n",
            'Providers/' . $module . 'ServiceProvider.php' => $this->provider($namespace, $module),
            'Routes/web.php' => $this->routes($namespace, $module, $slug),
            'Http/Controllers/' . $module . 'Controller.php' => $this->controller($namespace, $module),
            'Http/Middleware/' . $module . 'Middleware.php' => $this->middleware($namespace, $module),
            'Models/' . $module . '.php' => $this->model($namespace, $module, $table),
            'Database/Migrations/2026_01_01_000000_create_' . $table . '_table.php' => $this->migration($table),
            'Database/Seeders/' . $module . 'Seeder.php' => $this->seeder($module, $table),
            'Resources/views/layouts/app.php' => $this->viewLayout($module),
            'Resources/views/pages/index.php' => $this->viewIndex($module),
            'Resources/views/partials/.gitkeep' => '',
            'Resources/views/components/.gitkeep' => '',
            'Tests/' . $module . 'ModuleTest.php' => $this->test($module, $slug),
        ];
    }

    private function moduleManifest(string $module, string $slug): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

return [
    'name' => '{$module}',
    'slug' => '{$slug}',
    'providers' => [
        Modules\\{$module}\\Providers\\{$module}ServiceProvider::class,
    ],
    'routes' => 'modules/{$module}/Routes/web.php',
];

PHP;
    }

    private function provider(string $namespace, string $module): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\\Providers;

use WTD\Support\ServiceProvider;

final class {$module}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register {$module} services here.
    }

    public function boot(): void
    {
        // Boot {$module} services here.
    }
}

PHP;
    }

    private function routes(string $namespace, string $module, string $slug): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

use {$namespace}\\Http\\Controllers\\{$module}Controller;
use WTD\\Routing\\Router;

/** @var Router \$router */
\$router->get('/{$slug}', [{$module}Controller::class, 'index'])->name('{$slug}.index');

PHP;
    }

    private function controller(string $namespace, string $module): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\\Http\\Controllers;

use WTD\\Http\\Request;
use WTD\\Http\\Response;
use WTD\\View\\ViewRenderer;

final class {$module}Controller
{
    public function __construct(private readonly ViewRenderer \$views)
    {
    }

    /**
     * @param array<string, string> \$parameters
     */
    public function index(Request \$request, array \$parameters): Response
    {
        return Response::make(\$this->views->renderModule('{$module}', 'pages.index', [
            'module' => '{$module}',
        ]));
    }
}

PHP;
    }

    private function viewLayout(string $module): string
    {
        return <<<PHP
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$module}</title>
</head>
<body>
    {{ content }}
</body>
</html>

PHP;
    }

    private function viewIndex(string $module): string
    {
        return <<<PHP
<section>
    <h1>{{ module }}</h1>
    <p>{{ module }} module is ready.</p>
</section>

PHP;
    }

    private function middleware(string $namespace, string $module): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\\Http\\Middleware;

use Closure;
use WTD\\Http\\Request;
use WTD\\Http\\Response;
use WTD\\Middleware\\Middleware;

final class {$module}Middleware implements Middleware
{
    public function handle(Request \$request, Closure \$next): Response
    {
        return \$next(\$request);
    }
}

PHP;
    }

    private function model(string $namespace, string $module, string $table): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\\Models;

use WTD\\ORM\\Model;

final class {$module} extends Model
{
    protected ?string \$table = '{$table}';

    protected ?string \$connectionName = null;

    protected bool \$useTimestamps = true;

    protected bool \$protectFields = true;

    /**
     * @var list<string>
     */
    protected array \$allowedFields = [
        'name',
    ];
}

PHP;
    }

    private function migration(string $table): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

use WTD\\Database\\Blueprint;
use WTD\\Database\\Migration;
use WTD\\Database\\Schema;

return new class implements Migration {
    public function up(Schema \$schema): void
    {
        \$schema->create('{$table}', static function (Blueprint \$table): void {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
        });
    }

    public function down(Schema \$schema): void
    {
        \$schema->dropIfExists('{$table}');
    }
};

PHP;
    }

    private function seeder(string $module, string $table): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

use WTD\\Database\\Connection;
use WTD\\Database\\Seeder;

return new class implements Seeder {
    public function run(Connection \$connection): void
    {
        \$connection->table('{$table}')->insert([
            'name' => '{$module} example',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
};

PHP;
    }

    private function test(string $module, string $slug): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace Modules\\{$module}\\Tests;

use PHPUnit\\Framework\\TestCase;

final class {$module}ModuleTest extends TestCase
{
    public function testModuleMetadataIsValid(): void
    {
        \$manifest = require dirname(__DIR__) . '/module.php';

        self::assertSame('{$module}', \$manifest['name']);
        self::assertSame('{$slug}', \$manifest['slug']);
    }
}

PHP;
    }

    private function className(string $name): string
    {
        $base = preg_replace('/[^A-Za-z0-9_]+/', '', basename(str_replace('\\', '/', $name))) ?? '';

        if ($base === '') {
            return 'Module';
        }

        $base = str_replace('_', ' ', $base);
        $base = str_replace(' ', '', ucwords($base));

        return ctype_digit($base[0]) ? 'Module' . $base : $base;
    }

    private function slug(string $name): string
    {
        $slug = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug) ?? 'module';
        $slug = trim($slug, '-');

        return $slug === '' ? 'module' : $slug;
    }
}
