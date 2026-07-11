<?php

declare(strict_types=1);

namespace WTD\AI;

final class NullAiProvider implements AiProvider
{
    public function name(): string
    {
        return 'null';
    }

    public function complete(string $prompt): string
    {
        return $prompt;
    }
}
