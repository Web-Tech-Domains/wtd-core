<?php

declare(strict_types=1);

namespace WTD\Console;

use InvalidArgumentException;

/**
 * Thrown when a requested console command is not registered.
 */
final class UnknownCommandException extends InvalidArgumentException
{
}
