<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use WTD\Console\Input;

final class InputTest extends TestCase
{
    public function testInputParsesArgumentsAndOptions(): void
    {
        $input = new Input(['make:class', 'UserService', '--force', '--namespace=App\\Services']);

        self::assertSame('make:class', $input->commandName());
        self::assertSame(['UserService'], $input->arguments());
        self::assertSame('UserService', $input->argument(0));
        self::assertTrue($input->hasOption('force'));
        self::assertTrue($input->option('force'));
        self::assertSame('App\\Services', $input->option('namespace'));
        self::assertSame('fallback', $input->argument(1, 'fallback'));
    }

    public function testInputUsesDefaultCommandName(): void
    {
        self::assertSame('about', (new Input([]))->commandName());
    }
}
