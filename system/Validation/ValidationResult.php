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
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }
}
