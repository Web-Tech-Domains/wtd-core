<?php

declare(strict_types=1);

use WTD\Database\Connection;
use WTD\Database\Seeder;

return new class implements Seeder {
    public function run(Connection $connection): void
    {
        $now = date('Y-m-d H:i:s');

        $connection->table('forum_categories')->insert([
            'name' => 'Announcements',
            'slug' => 'announcements',
            'description' => 'Project news, releases, and community updates.',
            'sort_order' => 1,
            'is_locked' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $connection->table('forum_topics')->insert([
            'forum_category_id' => 1,
            'title' => 'Welcome to the WTD Core forums',
            'slug' => 'welcome-to-the-wtd-core-forums',
            'body' => 'Use this space to discuss framework usage, packages, and open-source contributions.',
            'author_name' => 'Web Tech Domains',
            'status' => 'pinned',
            'is_pinned' => true,
            'views' => 1,
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $connection->table('forum_posts')->insert([
            'forum_topic_id' => 1,
            'body' => 'Keep topics specific, respectful, and useful for future readers.',
            'author_name' => 'Web Tech Domains',
            'is_solution' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};

