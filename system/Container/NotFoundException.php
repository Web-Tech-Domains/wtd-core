<?php

declare(strict_types=1);

namespace WTD\Container;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Represents a missing service requested from the container.
 */
final class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
