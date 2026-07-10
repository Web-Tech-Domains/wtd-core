<?php

declare(strict_types=1);

namespace WTD\Validation;

/**
 * Holds the outcome of a validation pass.
 */
final class ValidationResult
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $errors
     * @param list<string> $fields
     */
    public function __construct(
        private readonly array $data,
        private readonly array $errors,
        private readonly array $fields,
    ) {
    }

    /**
     * Determine whether validation passed.
     */
    public function passes(): bool
    {
        return $this->errors === [];
    }

    /**
     * Determine whether validation failed.
     */
    public function fails(): bool
    {
        return !$this->passes();
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

    /**
     * Return validated input for fields that passed validation.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors);
        }

        $validated = [];

        foreach ($this->fields as $field) {
            if ($this->exists($field)) {
                $validated[$field] = $this->value($field);
            }
        }

        return $validated;
    }

    private function exists(string $field): bool
    {
        if (array_key_exists($field, $this->data)) {
            return true;
        }

        $current = $this->data;

        foreach (explode('.', $field) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        return true;
    }

    private function value(string $field): mixed
    {
        if (array_key_exists($field, $this->data)) {
            return $this->data[$field];
        }

        $current = $this->data;

        foreach (explode('.', $field) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }
}
