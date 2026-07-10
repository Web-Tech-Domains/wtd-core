<?php

declare(strict_types=1);

namespace WTD\Validation;

/**
 * Defines a reusable custom validation rule.
 */
interface Rule
{
    /**
     * Determine whether the value passes validation.
     *
     * @param array<string, mixed> $data
     */
    public function passes(string $field, mixed $value, ?string $parameter, array $data): bool;

    /**
     * Return the validation failure message.
     */
    public function message(string $field, ?string $parameter): string;
}
