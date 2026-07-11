<?php

declare(strict_types=1);

namespace WTD\Monitoring;

use WTD\Console\Commands\MonitorReportCommand;
use WTD\Console\Kernel;
use WTD\Http\Response;
use WTD\Routing\Router;
use WTD\Support\ServiceProvider;

final class MonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(MetricsRegistry::class);
        $this->container()->singleton(SystemMonitor::class);
        $this->container()->singleton(AdminDashboardRenderer::class);
    }

    public function boot(): void
    {
        if ($this->container()->has(Kernel::class)) {
            /** @var Kernel $kernel */
            $kernel = $this->container()->get(Kernel::class);
            $kernel->register($this->container()->get(MonitorReportCommand::class));
        }

        if (!(bool) $this->app->config()->get('monitoring.admin_enabled', false) || !$this->container()->has(Router::class)) {
            return;
        }

        $path = $this->app->config()->get('monitoring.admin_path', '/admin/system');
        $path = is_scalar($path) ? (string) $path : '/admin/system';

        /** @var Router $router */
        $router = $this->container()->get(Router::class);
        $router->get($path, fn (): Response => Response::make(
            $this->container()->get(AdminDashboardRenderer::class)->render(
                $this->container()->get(SystemMonitor::class)->report(),
            ),
        ))->name('admin.system');
    }
}
