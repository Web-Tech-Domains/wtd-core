<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Config\Repository;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;
use WTD\Http\Request;
use WTD\Kernel\HttpKernel;

final class BenchmarkCommand implements Command
{
    public function __construct(
        private readonly HttpKernel $kernel,
        private readonly Repository $config,
    ) {
    }

    public function name(): string
    {
        return 'benchmark';
    }

    public function description(): string
    {
        return 'Benchmark an HTTP path through the framework kernel.';
    }

    public function handle(Input $input, Output $output): int
    {
        $path = $input->argument(0, '/');
        $iterations = $this->iterations($input);
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->kernel->handle(new Request('GET', $path ?? '/'))->content();
        }

        $elapsed = (microtime(true) - $start) * 1000;

        $output->json([
            'path' => $path,
            'iterations' => $iterations,
            'total_ms' => round($elapsed, 3),
            'average_ms' => round($elapsed / $iterations, 3),
        ]);

        return 0;
    }

    private function iterations(Input $input): int
    {
        $configured = $this->config->get('developer.benchmark_iterations', 100);
        $value = $input->option('iterations', is_scalar($configured) ? (string) $configured : '100');
        $iterations = is_string($value) ? (int) $value : 100;

        return max(1, min(10000, $iterations));
    }
}
