<?php

declare(strict_types=1);

namespace WTD\Console\Commands;

use WTD\Application\Application;
use WTD\Console\Command;
use WTD\Console\Input;
use WTD\Console\Output;

final class ServeCommand implements Command
{
    public function __construct(private readonly Application $app)
    {
    }

    public function name(): string
    {
        return 'serve';
    }

    public function description(): string
    {
        return 'Start the local PHP development server.';
    }

    public function handle(Input $input, Output $output): int
    {
        $host = $this->host((string) $input->option('host', '127.0.0.1'));
        $port = $this->port((string) $input->option('port', '8000'));
        $publicPath = $this->app->basePath('public');
        $router = $publicPath . DIRECTORY_SEPARATOR . 'index.php';
        $command = implode(' ', [
            escapeshellarg(PHP_BINARY),
            '-S',
            escapeshellarg($host . ':' . $port),
            '-t',
            escapeshellarg($publicPath),
            escapeshellarg($router),
        ]);

        $output->line(sprintf('WTD Core development server: http://%s:%d', $host, $port));
        $output->line($command);

        if ($input->hasOption('no-run')) {
            return 0;
        }

        passthru($command, $status);

        return $status;
    }

    private function host(string $host): string
    {
        return preg_match('/^[A-Za-z0-9_.:-]+$/', $host) === 1 ? $host : '127.0.0.1';
    }

    private function port(string $port): int
    {
        $number = filter_var($port, FILTER_VALIDATE_INT, [
            'options' => [
                'default' => 8000,
                'min_range' => 1,
                'max_range' => 65535,
            ],
        ]);

        return is_int($number) ? $number : 8000;
    }
}
