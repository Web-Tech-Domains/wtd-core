<?php

declare(strict_types=1);

namespace WTD\Auth;

interface Authenticatable
{
    public function getAuthIdentifier(): mixed;

    public function getAuthPassword(): string;

    public function getRememberToken(): ?string;

    public function setRememberToken(?string $token): void;
}
