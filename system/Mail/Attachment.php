<?php

declare(strict_types=1);

namespace WTD\Mail;

final class Attachment
{
    public function __construct(
        public readonly string $path,
        public readonly ?string $name = null,
        public readonly bool $inline = false,
        public readonly ?string $contentId = null,
    ) {
    }
}
