<?php

declare(strict_types=1);

namespace WTD\Validation;

use Closure;

/**
 * Validates associative input arrays against pipe-style rules.
 */
final class Validator
{
    /**
     * @var array<string, mixed>
     */
    private array $activeData = [];

    /**
     * @var array<string, Rule|Closure(string, mixed, ?string, array<string, mixed>): bool>
     */
    private array $extensions = [];

    /**
     * @var array<string, string>
     */
    private array $extensionMessages = [];

    /**
     * Register a custom validation rule.
     *
     * @param Rule|Closure(string, mixed, ?string, array<string, mixed>): bool $rule
     */
    public function extend(string $name, Rule|Closure $rule, ?string $message = null): self
    {
        $this->extensions[$name] = $rule;

        if ($message !== null) {
            $this->extensionMessages[$name] = $message;
        }

        return $this;
    }

    /**
     * Build a validation result without throwing.
     *
     * @param array<string, mixed> $data
     * @param array<string, string|list<string>> $rules
     * @param array<string, string> $messages
     */
    public function make(array $data, array $rules, array $messages = []): ValidationResult
    {
        $errors = [];
        $this->activeData = $data;

        foreach ($rules as $field => $fieldRules) {
            $parsedRules = $this->parseRules($fieldRules);
            $exists = $this->exists($data, $field);
            $value = $this->value($data, $field);

            if ($this->hasRule($parsedRules, 'sometimes') && !$exists) {
                continue;
            }

            if ($this->hasRule($parsedRules, 'nullable') && $value === null) {
                continue;
            }

            if ($this->shouldSkipOptional($parsedRules, $exists, $value)) {
                continue;
            }

            foreach ($parsedRules as $rule) {
                $name = $rule[0];

                if (in_array($name, ['nullable', 'sometimes'], true)) {
                    continue;
                }

                if (!$this->passesRule($name, $rule[1] ?? null, $field, $value, $data, $exists)) {
                    $errors[$field][] = $this->message($field, $name, $rule[1] ?? null, $messages);
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
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>
     */
    public function validate(array $data, array $rules, array $messages = []): array
    {
        return $this->make($data, $rules, $messages)->validated();
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
        foreach ($rules as $rule) {
            if ($rule[0] === 'required') {
                return true;
            }

            if ($rule[0] === 'required_if' && $this->requiredIf($rule[1] ?? null)) {
                return true;
            }

            if ($rule[0] === 'required_unless' && $this->requiredUnless($rule[1] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{0: string, 1?: string}> $rules
     */
    private function shouldSkipOptional(array $rules, bool $exists, mixed $value): bool
    {
        if ($this->isRequired($rules)) {
            return false;
        }

        if (!$exists) {
            return !$this->hasRule($rules, 'present');
        }

        if ($this->isEmpty($value)) {
            return !$this->hasRule($rules, 'filled');
        }

        return false;
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
        if (array_key_exists($rule, $this->extensions)) {
            $extension = $this->extensions[$rule];

            if ($extension instanceof Rule) {
                return $extension->passes($field, $value, $parameter, $data);
            }

            return $extension($field, $value, $parameter, $data);
        }

        return match ($rule) {
            'required' => $exists && !$this->isEmpty($value),
            'required_if' => !$this->requiredIf($parameter) || ($exists && !$this->isEmpty($value)),
            'required_unless' => !$this->requiredUnless($parameter) || ($exists && !$this->isEmpty($value)),
            'present' => $exists,
            'filled' => !$exists || !$this->isEmpty($value),
            'accepted' => in_array($value, ['yes', 'on', '1', 1, true], true),
            'declined' => in_array($value, ['no', 'off', '0', 0, false], true),
            'string' => is_string($value),
            'integer' => is_int($value) || (is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false),
            'numeric' => is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)),
            'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
            'array' => is_array($value),
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false,
            'date' => is_string($value) && strtotime($value) !== false,
            'min' => $this->compareSize($value, $parameter, static fn (float $size, float $limit): bool => $size >= $limit),
            'max' => $this->compareSize($value, $parameter, static fn (float $size, float $limit): bool => $size <= $limit),
            'size' => $this->compareSize($value, $parameter, static fn (float $size, float $limit): bool => $size === $limit),
            'between' => $this->between($value, $parameter),
            'in' => $parameter !== null && in_array((string) $value, explode(',', $parameter), true),
            'not_in' => $parameter !== null && !in_array((string) $value, explode(',', $parameter), true),
            'regex' => $parameter !== null && is_string($value) && @preg_match($parameter, $value) === 1,
            'same' => $parameter !== null && $this->value($data, $parameter) === $value,
            'different' => $parameter !== null && $this->value($data, $parameter) !== $value,
            'confirmed' => $this->value($data, $field . '_confirmation') === $value,
            default => false,
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

    /**
     * @param array<string, string> $messages
     */
    private function message(string $field, string $rule, ?string $parameter, array $messages): string
    {
        if (array_key_exists($field . '.' . $rule, $messages)) {
            return $messages[$field . '.' . $rule];
        }

        if (array_key_exists($rule, $messages)) {
            return $messages[$rule];
        }

        if (array_key_exists($rule, $this->extensionMessages)) {
            return str_replace([':field', ':parameter'], [$field, $parameter ?? ''], $this->extensionMessages[$rule]);
        }

        if (array_key_exists($rule, $this->extensions) && $this->extensions[$rule] instanceof Rule) {
            return $this->extensions[$rule]->message($field, $parameter);
        }

        return sprintf('The %s field failed %s validation.', $field, $rule);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function exists(array $data, string $field): bool
    {
        if (array_key_exists($field, $data)) {
            return true;
        }

        $current = $data;

        foreach (explode('.', $field) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function value(array $data, string $field): mixed
    {
        if (array_key_exists($field, $data)) {
            return $data[$field];
        }

        $current = $data;

        foreach (explode('.', $field) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    private function requiredIf(?string $parameter): bool
    {
        if ($parameter === null) {
            return false;
        }

        [$field, $expected] = array_pad(explode(',', $parameter, 2), 2, null);

        return is_string($field) && is_string($expected) && (string) $this->value($this->activeData, $field) === $expected;
    }

    private function requiredUnless(?string $parameter): bool
    {
        if ($parameter === null) {
            return false;
        }

        [$field, $expected] = array_pad(explode(',', $parameter, 2), 2, null);

        return is_string($field) && is_string($expected) && (string) $this->value($this->activeData, $field) !== $expected;
    }
}
