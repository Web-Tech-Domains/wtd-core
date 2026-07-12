<?php

declare(strict_types=1);

namespace Modules\Forums\Models;

use WTD\ORM\Model;

final class ForumTopic extends Model
{
    protected ?string $table = 'forum_topics';

    protected bool $useTimestamps = true;

    protected bool $protectFields = true;

    /**
     * @var list<string>
     */
    protected array $allowedFields = [
        'forum_category_id',
        'title',
        'slug',
        'body',
        'author_name',
        'status',
        'is_pinned',
        'views',
        'last_activity_at',
    ];
}
