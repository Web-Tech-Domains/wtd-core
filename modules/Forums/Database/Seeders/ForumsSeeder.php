<?php

declare(strict_types=1);

use WTD\Database\Connection;
use WTD\Database\Seeder;

return new class implements Seeder {
    public function run(Connection $connection): void
    {
        $now = date('Y-m-d H:i:s');

        // Seed Categories
        $categories = [
            [
                'id' => 1,
                'name' => 'Announcements',
                'slug' => 'announcements',
                'description' => 'Project news, releases, and community updates.',
                'sort_order' => 1,
                'is_locked' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Framework Help',
                'slug' => 'framework-help',
                'description' => 'Get help with WTD Core features, configuration, and debugging.',
                'sort_order' => 2,
                'is_locked' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Packages',
                'slug' => 'packages',
                'description' => 'Discuss package development, integration, and marketplace.',
                'sort_order' => 3,
                'is_locked' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Security',
                'slug' => 'security',
                'description' => 'Report vulnerabilities and discuss security hardening.',
                'sort_order' => 4,
                'is_locked' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        foreach ($categories as $category) {
            $connection->table('forum_categories')->insert($category);
        }

        // Seed Topics
        $topics = [
            [
                'id' => 1,
                'forum_category_id' => 3, // Packages
                'title' => 'How should forum modules expose package hooks?',
                'slug' => 'how-should-forum-modules-expose-package-hooks',
                'body' => 'We need a standardized way for package modules to define hook hooks in WTD core.',
                'author_name' => 'Core Team',
                'status' => 'Open',
                'is_pinned' => 0,
                'views' => 312,
                'last_activity_at' => '12 min ago',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'forum_category_id' => 2, // Framework Help
                'title' => 'Best practices for model events and moderation logs',
                'slug' => 'best-practices-for-model-events-and-moderation-logs',
                'body' => 'Should we use observers or inline events for logging moderator actions?',
                'author_name' => 'Maintainer',
                'status' => 'Answered',
                'is_pinned' => 0,
                'views' => 144,
                'last_activity_at' => '1 hr ago',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'forum_category_id' => 1, // Announcements
                'title' => 'Proposal: changelog module release notes workflow',
                'slug' => 'proposal-changelog-module-release-notes-workflow',
                'body' => 'We are proposing a new workflow for generating release notes from git commits automatically.',
                'author_name' => 'Web Tech Domains',
                'status' => 'Pinned',
                'is_pinned' => 1,
                'views' => 401,
                'last_activity_at' => 'Today',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        foreach ($topics as $topic) {
            $connection->table('forum_topics')->insert($topic);
        }

        // Seed Posts (Replies)
        // Topic 1 has 18 replies
        for ($i = 1; $i <= 18; $i++) {
            $connection->table('forum_posts')->insert([
                'forum_topic_id' => 1,
                'body' => "Excellent point! Here is reply #{$i} to discuss module hooks architecture and design.",
                'author_name' => "Developer {$i}",
                'is_solution' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Topic 2 has 9 replies
        for ($i = 1; $i <= 9; $i++) {
            $connection->table('forum_posts')->insert([
                'forum_topic_id' => 2,
                'body' => "Reply #{$i} explaining best practices for observer patterns in models.",
                'author_name' => "Contributor {$i}",
                'is_solution' => $i === 5 ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Topic 3 has 24 replies
        for ($i = 1; $i <= 24; $i++) {
            $connection->table('forum_posts')->insert([
                'forum_topic_id' => 3,
                'body' => "Changelog release note suggestion #{$i} for automation.",
                'author_name' => "User {$i}",
                'is_solution' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
