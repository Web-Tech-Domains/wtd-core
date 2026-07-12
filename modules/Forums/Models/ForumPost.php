<?php

declare(strict_types=1);

namespace Modules\Forums\Models;

use WTD\ORM\Model;

final class ForumPost extends Model
{
    protected ?string $table = 'forum_posts';

    protected bool $useTimestamps = true;

    protected bool $protectFields = true;

    /**
     * @var list<string>
     */
    protected array $allowedFields = [
        'forum_topic_id',
        'body',
        'author_name',
        'is_solution',
    ];
}
