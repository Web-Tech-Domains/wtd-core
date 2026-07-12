<?php

declare(strict_types=1);

namespace Tests\Modules;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Filesystem\Filesystem;
use WTD\Http\HttpServiceProvider;
use WTD\Http\Request;
use WTD\Kernel\HttpKernel;
use WTD\Logging\Logger;
use WTD\Modules\ModuleServiceProvider;
use WTD\View\ViewServiceProvider;

final class ForumsModuleTest extends TestCase
{
    public function testForumsModuleIsDiscoveredAndRendersVueWorkspace(): void
    {
        $app = new Application(
            dirname(__DIR__, 2),
            new Container(),
            new Repository([
                'modules.auto_discover' => true,
                'modules.enabled' => [],
                'view.path' => 'resources/views',
                'view.extension' => '.php',
                'assets.manifest' => 'public/build/.vite/manifest.json',
                'assets.hot_file' => 'public/hot',
                'assets.dev_server' => 'http://127.0.0.1:5173',
                'http.middleware' => [],
                'app.debug' => false,
                'developer.error_pages' => true,
                'database.default' => 'sqlite',
                'database.connections.sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                ],
            ]),
        );

        $app->container()->singleton(Filesystem::class);
        $app->container()->singleton(Logger::class, fn (): Logger => new Logger($app->basePath('storage/logs/tests-forums.log')));
        $app->register(HttpServiceProvider::class);
        $app->register(ViewServiceProvider::class);
        $app->register(\WTD\Database\DatabaseServiceProvider::class);
        $app->register(ModuleServiceProvider::class);

        $GLOBALS['wtd_app'] = $app;

        try {
            $app->boot();

            /** @var \WTD\Database\MigrationRunner $migrator */
            $migrator = $app->container()->get(\WTD\Database\MigrationRunner::class);
            $migrator->migrate();

            /** @var \WTD\Database\SeederRunner $seeder */
            $seeder = $app->container()->get(\WTD\Database\SeederRunner::class);
            $seeder->run('ForumsSeeder');

            /** @var HttpKernel $kernel */
            $kernel = $app->container()->get(HttpKernel::class);
            $response = $kernel->handle(new Request('GET', '/forums'));

            self::assertSame(200, $response->status());
            self::assertStringContainsString('data-forums-app', $response->content());
            self::assertStringContainsString('/assets/modules/forums.css', $response->content());
            self::assertStringNotContainsString('/resources/js/modules/forums.js', $response->content());
            self::assertStringContainsString('Community topics', $response->content());
            self::assertStringContainsString('forums-initial-state', $response->content());
        } finally {
            unset($GLOBALS['wtd_app']);
        }
    }

    public function testForumsModuleAllowsTopicCreation(): void
    {
        $app = new Application(
            dirname(__DIR__, 2),
            new Container(),
            new Repository([
                'modules.auto_discover' => true,
                'modules.enabled' => [],
                'view.path' => 'resources/views',
                'view.extension' => '.php',
                'database.default' => 'sqlite',
                'database.connections.sqlite' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                ],
            ]),
        );

        $app->container()->singleton(Filesystem::class);
        $app->container()->singleton(Logger::class, fn (): Logger => new Logger($app->basePath('storage/logs/tests-forums-post.log')));
        $app->register(HttpServiceProvider::class);
        $app->register(ViewServiceProvider::class);
        $app->register(\WTD\Database\DatabaseServiceProvider::class);
        $app->register(ModuleServiceProvider::class);

        $GLOBALS['wtd_app'] = $app;

        try {
            $app->boot();

            /** @var \WTD\Database\MigrationRunner $migrator */
            $migrator = $app->container()->get(\WTD\Database\MigrationRunner::class);
            $migrator->migrate();

            /** @var \WTD\Database\SeederRunner $seeder */
            $seeder = $app->container()->get(\WTD\Database\SeederRunner::class);
            $seeder->run('ForumsSeeder');
            $seeder->run('UserSeeder');

            /** @var \WTD\Session\SessionStore $session */
            $session = $app->container()->get(\WTD\Session\SessionStore::class);
            $session->start();
            $session->put('forums_user_id', 1);
            $session->save();

            /** @var HttpKernel $kernel */
            $kernel = $app->container()->get(HttpKernel::class);

            // Post a new topic
            $request = new Request(
                'POST',
                '/forums/topics',
                [], // headers
                [], // query
                [
                    'title' => 'New Thread Title',
                    'body' => 'Details of the new discussion.',
                    'category' => 'Framework Help',
                ],
                ['wtd_session' => $session->id()] // cookies
            );

            $response = $kernel->handle($request);

            self::assertSame(201, $response->status());

            $data = json_decode($response->content(), true);
            self::assertSame('New Thread Title', $data['title']);
            self::assertSame('Framework Help', $data['category']);
            self::assertSame('Admin User', $data['author']);
            self::assertSame(1, $data['replies']);

            // Verify topic is in database
            $topic = \Modules\Forums\Models\ForumTopic::query()->where('title', 'New Thread Title')->first();
            self::assertNotNull($topic);
            self::assertSame('new-thread-title', $topic->getAttribute('slug'));

            // Verify post is in database
            $post = \Modules\Forums\Models\ForumPost::query()->where('forum_topic_id', $topic->getAttribute('id'))->first();
            self::assertNotNull($post);
            self::assertSame('Details of the new discussion.', $post->getAttribute('body'));
        } finally {
            unset($GLOBALS['wtd_app']);
        }
    }
}
