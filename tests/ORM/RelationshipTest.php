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
use WTD\ORM\ModelQueryBuilder;
use WTD\ORM\Relations\BelongsTo;
use WTD\ORM\Relations\HasMany;
use WTD\ORM\Relations\HasOne;

final class RelationshipTest extends TestCase
{
    protected function setUp(): void
    {
        $connection = $this->connection();
        OrmAuthor::setConnection($connection);
        OrmPost::setConnection($connection);
        OrmProfile::setConnection($connection);

        $schema = new Schema($connection);
        $schema->create('authors', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('active');
        });
        $schema->create('posts', static function (Blueprint $table): void {
            $table->id();
            $table->integer('author_id');
            $table->string('title');
        });
        $schema->create('profiles', static function (Blueprint $table): void {
            $table->id();
            $table->integer('author_id');
            $table->string('bio');
        });

        (new OrmAuthor(['name' => 'Taylor', 'active' => 1]))->save();
        (new OrmAuthor(['name' => 'Ada', 'active' => 0]))->save();
        (new OrmPost(['author_id' => 1, 'title' => 'First']))->save();
        (new OrmPost(['author_id' => 1, 'title' => 'Second']))->save();
        (new OrmPost(['author_id' => 2, 'title' => 'Other']))->save();
        (new OrmProfile(['author_id' => 1, 'bio' => 'Writes']))->save();
    }

    public function testHasManyReturnsRelatedModels(): void
    {
        $author = OrmAuthor::find(1);

        self::assertInstanceOf(OrmAuthor::class, $author);

        $posts = $author->posts()->getResults();

        self::assertCount(2, $posts);
        self::assertSame('First', $posts[0]->getAttribute('title'));
        self::assertSame('Second', $posts[1]->getAttribute('title'));
    }

    public function testHasOneReturnsRelatedModel(): void
    {
        $author = OrmAuthor::find(1);

        self::assertInstanceOf(OrmAuthor::class, $author);

        $profile = $author->profile()->getResult();

        self::assertInstanceOf(OrmProfile::class, $profile);
        self::assertSame('Writes', $profile->getAttribute('bio'));
    }

    public function testBelongsToReturnsParentModel(): void
    {
        $post = OrmPost::find(1);

        self::assertInstanceOf(OrmPost::class, $post);

        $author = $post->author()->getResult();

        self::assertInstanceOf(OrmAuthor::class, $author);
        self::assertSame('Taylor', $author->getAttribute('name'));
    }

    public function testLocalScopesFilterModelQueries(): void
    {
        /** @phpstan-ignore-next-line dynamic local scope call */
        $authors = OrmAuthor::query()->active()->get();

        self::assertCount(1, $authors);
        self::assertSame('Taylor', $authors[0]->getAttribute('name'));
    }

    private function connection(): Connection
    {
        return (new DatabaseManager(new Repository([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ])))->connection();
    }
}

final class OrmAuthor extends Model
{
    protected ?string $table = 'authors';

    /**
     * @return HasMany<OrmPost>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(OrmPost::class, 'author_id');
    }

    /**
     * @return HasOne<OrmProfile>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(OrmProfile::class, 'author_id');
    }

    /**
     * @param ModelQueryBuilder<self> $query
     *
     * @return ModelQueryBuilder<self>
     */
    public function scopeActive(ModelQueryBuilder $query): ModelQueryBuilder
    {
        return $query->where('active', 1);
    }
}

final class OrmPost extends Model
{
    protected ?string $table = 'posts';

    /**
     * @return BelongsTo<OrmAuthor>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(OrmAuthor::class, 'author_id');
    }
}

final class OrmProfile extends Model
{
    protected ?string $table = 'profiles';
}
