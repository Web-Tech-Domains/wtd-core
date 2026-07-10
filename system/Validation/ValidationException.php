<?php

declare(strict_types=1);

namespace WTD\Validation;

use InvalidArgumentException;

/**
 * Raised when validation fails.
 */
final class ValidationException extends InvalidArgumentException
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('The given data was invalid.');
    }

    /**
     * Return validation errors keyed by field.
     *
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
