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
            'Resources/views/partials/header.php' => $this->viewHeader($module),
            'Resources/views/partials/footer.php' => $this->viewFooter($module),
            'Resources/views/components/feature-card.php' => $this->viewFeatureCard(),
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
    <meta name="description" content="{$module} module for WTD Core applications.">
    <title>{$module} Module | WTD Core</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f9fc;
            --panel: #ffffff;
            --ink: #102032;
            --muted: #64748b;
            --line: #dbe3ec;
            --blue: #2563eb;
            --cyan: #0891b2;
            --green: #15803d;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); }
        a { color: inherit; text-decoration: none; }
        .module-shell { min-height: 100vh; display: flex; flex-direction: column; }
        .module-wrap { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .module-nav { border-bottom: 1px solid var(--line); background: rgba(255, 255, 255, .92); }
        .module-nav-row { min-height: 68px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .module-brand { display: flex; align-items: center; gap: 10px; font-weight: 800; color: #172554; }
        .module-mark { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: linear-gradient(135deg, var(--blue), var(--cyan)); }
        .module-links { display: flex; flex-wrap: wrap; gap: 10px; color: var(--muted); font-size: 14px; font-weight: 700; }
        .module-links a { padding: 8px 10px; border-radius: 8px; }
        .module-links a:hover { background: #eef6ff; color: var(--blue); }
        .module-main { flex: 1; }
        .module-hero { padding: 72px 0 46px; }
        .module-hero-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 32px; align-items: center; }
        .module-kicker { width: fit-content; display: inline-flex; align-items: center; min-height: 32px; padding: 0 12px; border-radius: 999px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 800; }
        .module-title { margin: 18px 0 14px; font-size: clamp(38px, 7vw, 68px); line-height: 1.04; letter-spacing: 0; }
        .module-lead { max-width: 680px; margin: 0; color: var(--muted); font-size: 18px; line-height: 1.7; }
        .module-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
        .module-button { min-height: 46px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; border-radius: 8px; border: 1px solid var(--line); background: #fff; font-weight: 800; }
        .module-button.primary { border-color: var(--blue); background: var(--blue); color: #fff; }
        .module-panel { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 22px; box-shadow: 0 18px 46px rgba(16, 32, 50, .08); }
        .module-status { display: grid; gap: 12px; margin-top: 16px; }
        .module-status div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line); color: var(--muted); }
        .module-status strong { color: var(--green); }
        .module-section { padding: 48px 0; }
        .module-section h2 { margin: 0 0 18px; font-size: clamp(26px, 4vw, 38px); letter-spacing: 0; }
        .module-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .module-card { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 20px; }
        .module-card span { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: var(--blue); font-weight: 900; }
        .module-card h3 { margin: 16px 0 8px; font-size: 18px; }
        .module-card p { margin: 0; color: var(--muted); line-height: 1.6; }
        .module-footer { border-top: 1px solid var(--line); padding: 22px 0; color: var(--muted); background: #fff; font-size: 14px; }

        @media (max-width: 820px) {
            .module-nav-row { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .module-hero-grid, .module-grid { grid-template-columns: 1fr; }
            .module-hero { padding-top: 44px; }
        }
    </style>
</head>
<body>
    <div class="module-shell">
        {{ content }}
    </div>
</body>
</html>

PHP;
    }

    private function viewIndex(string $module): string
    {
        $initial = $this->initial($module);

        return <<<PHP
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ module }} module for WTD Core applications.">
    <title>{{ module }} Module | WTD Core</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f9fc;
            --panel: #ffffff;
            --ink: #102032;
            --muted: #64748b;
            --line: #dbe3ec;
            --blue: #2563eb;
            --cyan: #0891b2;
            --green: #15803d;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); }
        a { color: inherit; text-decoration: none; }
        .wrap { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .nav { border-bottom: 1px solid var(--line); background: rgba(255, 255, 255, .92); }
        .nav-row { min-height: 68px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; color: #172554; }
        .mark { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: linear-gradient(135deg, var(--blue), var(--cyan)); }
        .links { display: flex; flex-wrap: wrap; gap: 10px; color: var(--muted); font-size: 14px; font-weight: 700; }
        .links a { padding: 8px 10px; border-radius: 8px; }
        .links a:hover { background: #eef6ff; color: var(--blue); }
        .hero { padding: 72px 0 46px; }
        .hero-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 32px; align-items: center; }
        .kicker { width: fit-content; display: inline-flex; align-items: center; min-height: 32px; padding: 0 12px; border-radius: 999px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 800; }
        h1 { margin: 18px 0 14px; font-size: clamp(38px, 7vw, 68px); line-height: 1.04; letter-spacing: 0; }
        .lead { max-width: 680px; margin: 0; color: var(--muted); font-size: 18px; line-height: 1.7; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
        .button { min-height: 46px; display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; border-radius: 8px; border: 1px solid var(--line); background: #fff; font-weight: 800; }
        .button.primary { border-color: var(--blue); background: var(--blue); color: #fff; }
        .panel { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 22px; box-shadow: 0 18px 46px rgba(16, 32, 50, .08); }
        .panel h2 { margin: 0; font-size: 22px; }
        .status { display: grid; gap: 12px; margin-top: 16px; }
        .status div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line); color: var(--muted); }
        .status strong { color: var(--green); }
        .section { padding: 48px 0; }
        .section h2 { margin: 0 0 18px; font-size: clamp(26px, 4vw, 38px); letter-spacing: 0; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .card { border: 1px solid var(--line); border-radius: 8px; background: var(--panel); padding: 20px; }
        .card span { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 8px; color: #fff; background: var(--blue); font-weight: 900; }
        .card h3 { margin: 16px 0 8px; font-size: 18px; }
        .card p { margin: 0; color: var(--muted); line-height: 1.6; }
        footer { border-top: 1px solid var(--line); padding: 22px 0; color: var(--muted); background: #fff; font-size: 14px; }

        @media (max-width: 820px) {
            .nav-row { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .hero-grid, .grid { grid-template-columns: 1fr; }
            .hero { padding-top: 44px; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="wrap nav-row">
            <a class="brand" href="/">
                <span class="mark">{$initial}</span>
                <span>{{ module }} Module</span>
            </a>
            <nav class="links" aria-label="{{ module }} module navigation">
                <a href="/">Home</a>
                <a href="/health">Health</a>
                <a href="/docs/api">API Docs</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="wrap hero-grid">
                <div>
                    <span class="kicker">WTD Core module</span>
                    <h1>{{ module }} is ready.</h1>
                    <p class="lead">This generated module includes routing, controller dispatch, middleware, model, migration, seeder, tests, and a polished view foundation for real application work.</p>
                    <div class="actions">
                        <a class="button primary" href="/docs/api">Open API Docs</a>
                        <a class="button" href="/health">Check Runtime</a>
                    </div>
                </div>
                <aside class="panel" aria-label="{{ module }} module status">
                    <h2>Module status</h2>
                    <div class="status">
                        <div><span>Route</span><strong>registered</strong></div>
                        <div><span>Controller</span><strong>ready</strong></div>
                        <div><span>Views</span><strong>designed</strong></div>
                        <div><span>Tests</span><strong>generated</strong></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="section">
            <div class="wrap">
                <h2>Build from a complete structure.</h2>
                <div class="grid">
                    <article class="card">
                        <span>R</span>
                        <h3>Routes and controllers</h3>
                        <p>Start with a web route and controller that renders through the framework view service.</p>
                    </article>
                    <article class="card">
                        <span>D</span>
                        <h3>Data layer</h3>
                        <p>Use the generated model, migration, and seeder as the module's first persistence boundary.</p>
                    </article>
                    <article class="card">
                        <span>V</span>
                        <h3>View foundation</h3>
                        <p>Layout, page, partial, and component templates are included so the UI starts clean.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="wrap">{{ module }} Module for WTD Core by Web Tech Domains.</div>
    </footer>
</body>
</html>

PHP;
    }

    private function viewHeader(string $module): string
    {
        $initial = $this->initial($module);

        return <<<PHP
<header class="module-nav">
    <div class="module-wrap module-nav-row">
        <a class="module-brand" href="/">
            <span class="module-mark">{$initial}</span>
            <span>{$module} Module</span>
        </a>
        <nav class="module-links" aria-label="{$module} module navigation">
            <a href="/">Home</a>
            <a href="/health">Health</a>
            <a href="/docs/api">API Docs</a>
        </nav>
    </div>
</header>

PHP;
    }

    private function viewFooter(string $module): string
    {
        return <<<PHP
<footer class="module-footer">
    <div class="module-wrap">{$module} Module for WTD Core by Web Tech Domains.</div>
</footer>

PHP;
    }

    private function viewFeatureCard(): string
    {
        return <<<'PHP'
<article class="module-card">
    <span>{{ icon }}</span>
    <h3>{{ title }}</h3>
    <p>{{ description }}</p>
</article>

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

    private function initial(string $name): string
    {
        return strtoupper($name[0] ?? 'M');
    }
}
