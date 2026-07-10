<?php

declare(strict_types=1);

namespace WTD\Container;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Represents a dependency resolution failure inside the container.
 */
final class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
