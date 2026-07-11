<?php

declare(strict_types=1);

namespace WTD\CLI;

use WTD\Console\Input;
use WTD\Console\Kernel;
use WTD\Console\Output;
use WTD\Console\UnknownCommandException;

/**
 * Runs the console kernel from executable CLI input.
 */
final class CliApplication
{
    public function __construct(private readonly Kernel $kernel)
    {
    }

    /**
     * @param list<string> $argv
     */
    public function run(array $argv, Output $output): int
    {
        try {
            return $this->kernel->handle(Input::fromArgv($argv), $output);
        } catch (UnknownCommandException $exception) {
            $output->error($exception->getMessage());

            return 1;
        }
    }
}
