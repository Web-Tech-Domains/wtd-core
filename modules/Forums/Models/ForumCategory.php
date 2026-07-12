<?php

declare(strict_types=1);

namespace Modules\Forums\Models;

use WTD\ORM\Model;

final class ForumCategory extends Model
{
    protected ?string $table = 'forum_categories';

    protected bool $useTimestamps = true;

    protected bool $protectFields = true;

    /**
     * @var list<string>
     */
    protected array $allowedFields = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_locked',
    ];
}
