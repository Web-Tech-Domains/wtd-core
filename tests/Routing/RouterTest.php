<?php

declare(strict_types=1);

namespace Tests\Routing;

use PHPUnit\Framework\TestCase;
use WTD\Container\Container;
use WTD\Exception\MethodNotAllowedHttpException;
use WTD\Exception\NotFoundHttpException;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Routing\ControllerDispatcher;
use WTD\Routing\Router;
use WTD\Routing\UrlGenerator;

final class RouterTest extends TestCase
{
    public function testRouterDispatchesMatchingRoute(): void
    {
        $router = $this->router();
        $router->get('/hello', static fn (): string => 'Hello');

        $response = $router->dispatch(new Request('GET', '/hello'));

        self::assertSame(200, $response->status());
        self::assertSame('Hello', $response->content());
    }

    public function testRouterSupportsParametersAndJsonResponses(): void
    {
        $router = $this->router();
        $router->get('/users/{id}', static fn (Request $request, array $parameters): array => [
            'id' => $parameters['id'],
        ]);

        $response = $router->dispatch(new Request('GET', '/users/42'));

        self::assertSame('{"id":"42"}', $response->content());
    }

    public function testRouterReturnsNotFoundResponse(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->router()->dispatch(new Request('GET', '/missing'));
    }

    public function testRouterThrowsMethodNotAllowedForMatchingPath(): void
    {
        $router = $this->router();
        $router->get('/submit', static fn (): string => 'GET');

        try {
            $router->dispatch(new Request('POST', '/submit'));
            self::fail('Expected method not allowed exception.');
        } catch (MethodNotAllowedHttpException $exception) {
            self::assertSame(['GET', 'HEAD', 'OPTIONS'], $exception->allowedMethods());
            self::assertSame('GET, HEAD, OPTIONS', $exception->headers()['Allow']);
        }
    }

    public function testRouterSupportsAdditionalHttpMethods(): void
    {
        $router = $this->router();
        $router->put('/resource', static fn (): string => 'PUT');
        $router->patch('/resource', static fn (): string => 'PATCH');
        $router->delete('/resource', static fn (): string => 'DELETE');

        self::assertSame('PUT', $router->dispatch(new Request('PUT', '/resource'))->content());
        self::assertSame('PATCH', $router->dispatch(new Request('PATCH', '/resource'))->content());
        self::assertSame('DELETE', $router->dispatch(new Request('DELETE', '/resource'))->content());
    }

    public function testRouterSupportsImplicitHeadForGetRoutes(): void
    {
        $router = $this->router();
        $router->get('/headable', static fn (): string => 'HEAD OK');

        self::assertSame('HEAD OK', $router->dispatch(new Request('HEAD', '/headable'))->content());
    }

    public function testRouterAutomaticallyHandlesOptionsRequests(): void
    {
        $router = $this->router();
        $router->get('/options', static fn (): string => 'GET');
        $router->post('/options', static fn (): string => 'POST');

        $response = $router->dispatch(new Request('OPTIONS', '/options'));

        self::assertSame(204, $response->status());
        self::assertSame('', $response->content());
        self::assertSame('GET, HEAD, OPTIONS, POST', $response->headers()['Allow']);
    }

    public function testRouterSupportsGroupsAndNamedRoutes(): void
    {
        $router = $this->router();
        $router->group('/api', static function (Router $router): void {
            $router->get('/users/{id}', static fn (Request $request, array $parameters): string => $parameters['id'])
                ->name('api.users.show');
        });

        $response = $router->dispatch(new Request('GET', '/api/users/10'));

        self::assertSame('10', $response->content());
        self::assertSame('/api/users/{id}', $router->route('api.users.show')?->path());
    }

    public function testUrlGeneratorBuildsNamedRoutePaths(): void
    {
        $router = $this->router();
        $router->get('/users/{id}', static fn (): string => 'User')->name('users.show');

        $url = (new UrlGenerator($router))->route('users.show', [
            'id' => 42,
            'tab' => 'profile',
        ]);

        self::assertSame('/users/42?tab=profile', $url);
    }

    public function testRouterDispatchesControllerActions(): void
    {
        $router = $this->router();
        $router->get('/controller/{id}', [ExampleController::class, 'show']);

        $response = $router->dispatch(new Request('GET', '/controller/55'));

        self::assertSame('Controller 55', $response->content());
    }

    public function testRouterDispatchesInvokableControllers(): void
    {
        $router = $this->router();
        $router->get('/invokable', InvokableController::class);

        $response = $router->dispatch(new Request('GET', '/invokable'));

        self::assertSame('Invokable', $response->content());
    }

    public function testRouterSupportsDomainRoutes(): void
    {
        $router = $this->router();
        $router->domain('api.example.test', static function (Router $router): void {
            $router->get('/status', static fn (): string => 'Domain OK');
        });

        $matched = $router->dispatch(new Request('GET', '/status', ['host' => 'api.example.test']));

        self::assertSame('Domain OK', $matched->content());

        $this->expectException(NotFoundHttpException::class);
        $router->dispatch(new Request('GET', '/status', ['host' => 'www.example.test']));
    }

    public function testRouterSupportsApiVersionGroups(): void
    {
        $router = $this->router();
        $router->version('1', static function (Router $router): void {
            $router->get('/status', static fn (): string => 'v1');
        });

        self::assertSame('v1', $router->dispatch(new Request('GET', '/api/v1/status'))->content());
    }

    private function router(): Router
    {
        $container = new Container();
        $container->instance(Container::class, $container);

        return new Router(new ControllerDispatcher($container));
    }
}

final class ExampleController
{
    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters): string
    {
        return 'Controller ' . $parameters['id'];
    }
}

final class InvokableController
{
    /**
     * @param array<string, string> $parameters
     */
    public function __invoke(Request $request, array $parameters): string
    {
        return 'Invokable';
    }
}
