<?php

declare(strict_types=1);

namespace App\Models;

use WTD\ORM\Model;

final class User extends Model
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
}
