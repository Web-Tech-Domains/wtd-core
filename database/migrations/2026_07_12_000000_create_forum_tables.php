<?php

declare(strict_types=1);

use WTD\Database\Blueprint;
use WTD\Database\Migration;
use WTD\Database\Schema;

return new class implements Migration {
    public function up(Schema $schema): void
    {
        $schema->create('forum_categories', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->integer('sort_order');
            $table->boolean('is_locked');
            $table->timestamps();
        });

        $schema->create('forum_topics', static function (Blueprint $table): void {
            $table->id();
            $table->integer('forum_category_id');
            $table->string('title');
            $table->string('slug');
            $table->text('body');
            $table->string('author_name');
            $table->string('status');
            $table->boolean('is_pinned');
            $table->integer('views');
            $table->string('last_activity_at');
            $table->timestamps();
        });

        $schema->create('forum_posts', static function (Blueprint $table): void {
            $table->id();
            $table->integer('forum_topic_id');
            $table->text('body');
            $table->string('author_name');
            $table->boolean('is_solution');
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('forum_posts');
        $schema->dropIfExists('forum_topics');
        $schema->dropIfExists('forum_categories');
    }
};
