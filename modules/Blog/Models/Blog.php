<?php

declare(strict_types=1);

namespace Modules\Blog\Models;

use WTD\ORM\Model;

final class Blog extends Model
{
    protected ?string $table = 'blogs';

    protected ?string $connectionName = null;

    protected bool $useTimestamps = true;

    protected bool $protectFields = true;

    /**
     * @var list<string>
     */
    protected array $allowedFields = [
        'name',
    ];
}
