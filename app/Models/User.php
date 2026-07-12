<?php

declare(strict_types=1);

namespace App\Models;

use WTD\ORM\Model;
use WTD\Auth\Authenticatable;

final class User extends Model implements Authenticatable
{
    protected ?string $table = 'users';

    protected ?string $connectionName = null;

    protected bool $useTimestamps = true;

    protected bool $protectFields = true;

    /**
     * @var list<string>
     */
    protected array $allowedFields = [
        'name',
        'email',
        'password',
    ];

    public function getAuthIdentifier(): mixed
    {
        return $this->getAttribute('id');
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken(?string $token): void
    {
    }

    public function getAuthPassword(): string
    {
        return (string) $this->getAttribute('password');
    }
}
