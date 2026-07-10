<?php

declare(strict_types=1);

namespace Tests\ORM;

use PHPUnit\Framework\TestCase;
use WTD\Config\Repository;
use WTD\Database\Blueprint;
use WTD\Database\Connection;
use WTD\Database\DatabaseManager;
use WTD\Database\Schema;
use WTD\ORM\Model;
use WTD\ORM\Relations\BelongsToMany;
use WTD\ORM\Relations\MorphMany;
use WTD\ORM\Relations\MorphTo;

final class AdvancedRelationshipTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = (new DatabaseManager(new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ])))->connection();

        AdvancedUser::setConnection($this->connection);
        AdvancedRole::setConnection($this->connection);
        AdvancedPost::setConnection($this->connection);
        AdvancedComment::setConnection($this->connection);

        $schema = new Schema($this->connection);
        $schema->create('advanced_users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        $schema->create('advanced_roles', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        $schema->create('advanced_posts', static function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        $schema->create('advanced_comments', static function (Blueprint $table): void {
            $table->id();
            $table->string('body');
            $table->string('commentable_type');
            $table->integer('commentable_id');
        });
        $this->connection->statement('CREATE TABLE advanced_role_user (user_id INTEGER NOT NULL, role_id INTEGER NOT NULL)');
    }

    public function testBelongsToManyCanAttachListAndDetachRelatedModels(): void
    {
        $user = new AdvancedUser(['name' => 'Taylor']);
        $admin = new AdvancedRole(['name' => 'Admin']);
        $editor = new AdvancedRole(['name' => 'Editor']);
        $user->save();
        $admin->save();
        $editor->save();

        self::assertTrue($user->roles()->attach($admin->getKey()));
        self::assertTrue($user->roles()->attach($editor->getKey()));

        $roles = $user->roles()->getResults();

        self::assertSame(['Admin', 'Editor'], array_map(
            static fn (AdvancedRole $role): mixed => $role->getAttribute('name'),
            $roles,
        ));

        self::assertTrue($user->roles()->detach($admin->getKey()));
        self::assertSame(['Editor'], array_map(
            static fn (AdvancedRole $role): mixed => $role->getAttribute('name'),
            $user->roles()->getResults(),
        ));
    }

    public function testMorphManyAndMorphToResolvePolymorphicModels(): void
    {
        $post = new AdvancedPost(['title' => 'Release']);
        $post->save();
        $comment = new AdvancedComment([
            'body' => 'Ready',
            'commentable_type' => AdvancedPost::class,
            'commentable_id' => $post->getKey(),
        ]);
        $comment->save();

        $comments = $post->comments()->getResults();

        self::assertCount(1, $comments);
        self::assertSame('Ready', $comments[0]->getAttribute('body'));
        self::assertSame('Release', $comments[0]->commentable()->getResult()?->getAttribute('title'));
    }
}

final class AdvancedUser extends Model
{
    protected ?string $table = 'advanced_users';

    /**
     * @return BelongsToMany<AdvancedRole>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdvancedRole::class, 'advanced_role_user', 'user_id', 'role_id');
    }
}

final class AdvancedRole extends Model
{
    protected ?string $table = 'advanced_roles';
}

final class AdvancedPost extends Model
{
    protected ?string $table = 'advanced_posts';

    /**
     * @return MorphMany<AdvancedComment>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(AdvancedComment::class, 'commentable');
    }
}

final class AdvancedComment extends Model
{
    protected ?string $table = 'advanced_comments';

    public function commentable(): MorphTo
    {
        return $this->morphTo('commentable');
    }
}
