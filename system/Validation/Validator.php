<?php

declare(strict_types=1);

namespace WTD\Validation;

/**
 * Validates associative input arrays against pipe-style rules.
 */
final class Validator
{
    /**
     * Build a validation result without throwing.
     *
     * @param array<string, mixed> $data
     * @param array<string, string|list<string>> $rules
     */
    public function make(array $data, array $rules): ValidationResult
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $parsedRules = $this->parseRules($fieldRules);
            $exists = array_key_exists($field, $data);
            $value = $data[$field] ?? null;

            if (!$this->isRequired($parsedRules) && (!$exists || $this->isEmpty($value))) {
                continue;
            }

            foreach ($parsedRules as $rule) {
                $name = $rule[0];

                if ($name === 'nullable') {
                    continue;
                }

                if (!$this->passesRule($name, $rule[1] ?? null, $field, $value, $data, $exists)) {
                    $errors[$field][] = $this->message($field, $name);
                }
            }
        }

        return new ValidationResult($data, $errors, array_keys($rules));
    }

    /**
     * Validate input and return only validated fields.
     *
     * @param array<string, mixed> $data
     * @param array<string, string|list<string>> $rules
     *
     * @return array<string, mixed>
     */
    public function validate(array $data, array $rules): array
    {
        return $this->make($data, $rules)->validated();
    }

    /**
     * @param string|list<string> $rules
     *
     * @return list<array{0: string, 1?: string}>
     */
    private function parseRules(string|array $rules): array
    {
        $parts = is_string($rules) ? explode('|', $rules) : $rules;
        $parsed = [];

        foreach ($parts as $rule) {
            $segments = explode(':', $rule, 2);
            $name = trim($segments[0]);

            if ($name === '') {
                continue;
            }

            $parsed[] = isset($segments[1]) ? [$name, $segments[1]] : [$name];
        }

        return $parsed;
    }

    /**
     * @param list<array{0: string, 1?: string}> $rules
     */
    private function isRequired(array $rules): bool
    {
        return $this->hasRule($rules, 'required');
    }

    /**
     * @param list<array{0: string, 1?: string}> $rules
     */
    private function hasRule(array $rules, string $name): bool
    {
        foreach ($rules as $rule) {
            if ($rule[0] === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function passesRule(string $rule, ?string $parameter, string $field, mixed $value, array $data, bool $exists): bool
    {
        return match ($rule) {
            'required' => $exists && !$this->isEmpty($value),
            'string' => is_string($value),
            'integer' => is_int($value) || (is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false),
            'numeric' => is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)),
            'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
            'array' => is_array($value),
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'min' => $this->compareSize($value, $parameter, static fn (float $size, float $limit): bool => $size >= $limit),
            'max' => $this->compareSize($value, $parameter, static fn (float $size, float $limit): bool => $size <= $limit),
            'between' => $this->between($value, $parameter),
            'in' => $parameter !== null && in_array((string) $value, explode(',', $parameter), true),
            'confirmed' => array_key_exists($field . '_confirmation', $data) && $data[$field . '_confirmation'] === $value,
            default => true,
        };
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    /**
     * @param callable(float, float): bool $compare
     */
    private function compareSize(mixed $value, ?string $parameter, callable $compare): bool
    {
        if ($parameter === null || !is_numeric($parameter)) {
            return false;
        }

        return $compare($this->size($value), (float) $parameter);
    }

    private function between(mixed $value, ?string $parameter): bool
    {
        if ($parameter === null) {
            return false;
        }

        [$minimum, $maximum] = array_pad(explode(',', $parameter, 2), 2, null);

        if (!is_numeric($minimum) || !is_numeric($maximum)) {
            return false;
        }

        $size = $this->size($value);

        return $size >= (float) $minimum && $size <= (float) $maximum;
    }

    private function size(mixed $value): float
    {
        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
            return (float) $value;
        }

        if (is_array($value)) {
            return (float) count($value);
        }

        if (is_string($value)) {
            return (float) strlen($value);
        }

        return 0.0;
    }

    private function message(string $field, string $rule): string
    {
        return sprintf('The %s field failed %s validation.', $field, $rule);
    }
}
