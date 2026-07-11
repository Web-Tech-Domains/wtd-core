<?php

declare(strict_types=1);

namespace WTD\Security;

final class Xss
{
    public function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function escapeArray(array $input): array
    {
        foreach ($input as $key => $value) {
            $input[$key] = is_string($value) ? $this->escape($value) : (is_array($value) ? $this->escapeArray($value) : $value);
        }

        return $input;
    }
}
