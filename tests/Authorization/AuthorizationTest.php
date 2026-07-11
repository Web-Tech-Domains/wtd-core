<?php

declare(strict_types=1);

namespace Tests\Authorization;

use PHPUnit\Framework\TestCase;
use WTD\Auth\Authenticatable;
use WTD\Application\Application;
use WTD\Auth\GenericUser;
use WTD\Authorization\Acl;
use WTD\Authorization\AuthorizationServiceProvider;
use WTD\Authorization\Gate;
use WTD\Authorization\PolicyRegistry;
use WTD\Authorization\RbacManager;
use WTD\Authorization\RoleCache;
use WTD\Config\Repository;
use WTD\Container\Container;

final class AuthorizationTest extends TestCase
{
    public function testRbacAssignsRolesAndPermissions(): void
    {
        $rbac = new RbacManager();
        $rbac->assignRole(1, 'admin');
        $rbac->givePermissionToRole('admin', 'posts.update');

        self::assertTrue($rbac->hasRole(1, 'admin'));
        self::assertTrue($rbac->can(1, 'posts.update'));
        self::assertFalse($rbac->can(1, 'posts.delete'));
        self::assertSame(['admin'], $rbac->roles(1));
    }

    public function testRoleCacheStoresAndFlushesRoles(): void
    {
        $cache = new RoleCache();
        $cache->put(1, ['admin']);

        self::assertSame(['admin'], $cache->get(1));

        $cache->forget(1);
        self::assertNull($cache->get(1));

        $cache->put(1, ['editor']);
        $cache->flush();
        self::assertNull($cache->get(1));
    }

    public function testGateAllowsAndDeniesAbilities(): void
    {
        $gate = new Gate();
        $user = $this->user();
        $gate->define('posts.update', static fn (?Authenticatable $actor, mixed ...$arguments): bool => $actor?->getAuthIdentifier() === ($arguments[0] ?? null));

        self::assertTrue($gate->allows($user, 'posts.update', 1));
        self::assertTrue($gate->denies($user, 'posts.update', 2));
    }

    public function testPolicyRegistryDispatchesPolicyAbilities(): void
    {
        $registry = new PolicyRegistry();
        $registry->policy(PostResource::class, new PostPolicy());

        self::assertTrue($registry->allows($this->user(), 'update', new PostResource(1)));
        self::assertFalse($registry->allows($this->user(), 'update', new PostResource(2)));
    }

    public function testAclAllowsExplicitAndWildcardRules(): void
    {
        $acl = new Acl();
        $acl->allow('admin', 'posts', '*');
        $acl->allow('editor', 'posts', 'update');

        self::assertTrue($acl->allows('admin', 'posts', 'delete'));
        self::assertTrue($acl->allows('editor', 'posts', 'update'));
        self::assertTrue($acl->denies('editor', 'posts', 'delete'));
    }

    public function testAuthorizationServiceProviderRegistersServices(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application($basePath, new Container(), new Repository());
        $app->register(AuthorizationServiceProvider::class);

        self::assertInstanceOf(RbacManager::class, $app->container()->get(RbacManager::class));
        self::assertInstanceOf(Gate::class, $app->container()->get(Gate::class));
        self::assertInstanceOf(PolicyRegistry::class, $app->container()->get(PolicyRegistry::class));
        self::assertInstanceOf(Acl::class, $app->container()->get(Acl::class));
    }

    private function user(): GenericUser
    {
        return new GenericUser(['id' => 1, 'password' => '']);
    }
}

final class PostResource
{
    public function __construct(public readonly int $ownerId)
    {
    }
}

final class PostPolicy
{
    public function update(?GenericUser $user, PostResource $post): bool
    {
        return $user?->getAuthIdentifier() === $post->ownerId;
    }
}
