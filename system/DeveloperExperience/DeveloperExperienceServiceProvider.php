<?php

declare(strict_types=1);

namespace WTD\DeveloperExperience;

use WTD\Console\Commands\ApiDocsCommand;
use WTD\Console\Commands\BenchmarkCommand;
use WTD\Console\Commands\IdeHelperCommand;
use WTD\Console\Commands\MakeResourceCommand;
use WTD\Console\Kernel;
use WTD\Http\Response;
use WTD\Routing\Router;
use WTD\Support\ServiceProvider;

/**
 * Registers developer experience tooling.
 */
final class DeveloperExperienceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(Profiler::class);
        $this->container()->singleton(ErrorPageRenderer::class);
        $this->container()->singleton(OpenApiGenerator::class);
        $this->container()->singleton(ApiDocumentationRenderer::class);
        $this->container()->singleton(CodeGenerator::class);
    }

    public function boot(): void
    {
        if ($this->container()->has(Kernel::class)) {
            /** @var Kernel $kernel */
            $kernel = $this->container()->get(Kernel::class);
            $kernel->register($this->container()->get(ApiDocsCommand::class));
            $kernel->register($this->container()->get(BenchmarkCommand::class));
            $kernel->register($this->container()->get(IdeHelperCommand::class));
            $kernel->register($this->container()->get(MakeResourceCommand::class));
        }

        if (!(bool) $this->app->config()->get('developer.api_docs', false) || !$this->container()->has(Router::class)) {
            return;
        }

        /** @var Router $router */
        $router = $this->container()->get(Router::class);
        $router->get('/docs/openapi.json', fn (): Response => Response::json(
            $this->container()->get(OpenApiGenerator::class)->generate(),
        ))->name('docs.openapi');
        $router->get('/docs/api', fn (): Response => Response::make(
            $this->container()->get(ApiDocumentationRenderer::class)->render(),
        ))->name('docs.api');
    }
}
