<?php

declare(strict_types=1);

namespace WTD\AI;

interface AiProvider
{
    public function name(): string;

    public function complete(string $prompt): string;
}
