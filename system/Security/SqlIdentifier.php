<?php

declare(strict_types=1);

namespace WTD\Security;

use InvalidArgumentException;

final class SqlIdentifier
{
    public function assertSafe(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $identifier)) {
            throw new InvalidArgumentException(sprintf('SQL identifier [%s] is not safe.', $identifier));
        }

        return $identifier;
    }
}
