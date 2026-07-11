<?php

declare(strict_types=1);

namespace WTD\DeveloperExperience;

/**
 * Builds small application code templates for developer tooling.
 */
final class CodeGenerator
{
    public function apiResourceController(string $class, string $model): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\\{$model};
use WTD\Http\Request;

final class {$class}
{
    public function index(): array
    {
        return {$model}::query()->get();
    }

    public function show(Request \$request, array \$parameters): array
    {
        return {$model}::query()->where('id', '=', \$parameters['id'] ?? '')->first() ?? [];
    }
}

PHP;
    }

    public function apiResourceRoutes(string $uri, string $controller): string
    {
        return "\$router->get('{$uri}', [App\\Http\\Controllers\\{$controller}::class, 'index']);\n"
            . "\$router->get('{$uri}/{id}', [App\\Http\\Controllers\\{$controller}::class, 'show']);\n";
    }
}
