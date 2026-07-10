<?php

declare(strict_types=1);

namespace Tests\Routing;

use Closure;
use PHPUnit\Framework\TestCase;
use WTD\Container\Container;
use WTD\Exception\MethodNotAllowedHttpException;
use WTD\Exception\NotFoundHttpException;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Middleware\Middleware;
use WTD\Middleware\MiddlewareResolver;
use WTD\Middleware\Pipeline;
use WTD\Routing\ControllerDispatcher;
use WTD\Routing\Router;
use WTD\Routing\UrlGenerator;
use WTD\Validation\FormRequest;
use WTD\Validation\ValidationException;
use WTD\Validation\Validator;

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

    public function testRouterRunsRouteMiddleware(): void
    {
        $router = $this->routerWithMiddleware();
        $router->get('/guarded', static fn (): string => 'Guarded')
            ->middleware(RouteHeaderMiddleware::class);

        $response = $router->dispatch(new Request('GET', '/guarded'));

        self::assertSame('Guarded', $response->content());
        self::assertSame('yes', $response->headers()['X-Route-Middleware']);
    }

    public function testRouterInjectsFormRequestsIntoControllers(): void
    {
        $router = $this->routerWithValidation();
        $router->post('/users', [FormRequestController::class, 'store']);

        $response = $router->dispatch(new Request('POST', '/users', body: [
            'name' => 'Taylor',
            'email' => 'taylor@example.test',
        ]));

        self::assertSame('{"name":"Taylor","email":"taylor@example.test"}', $response->content());
    }

    public function testRouterThrowsValidationExceptionForInvalidFormRequests(): void
    {
        $this->expectException(ValidationException::class);

        $router = $this->routerWithValidation();
        $router->post('/users', [FormRequestController::class, 'store']);

        $router->dispatch(new Request('POST', '/users', body: [
            'name' => 'Taylor',
            'email' => 'invalid',
        ]));
    }

    private function router(): Router
    {
        $container = new Container();
        $container->instance(Container::class, $container);

        return new Router(new ControllerDispatcher($container));
    }

    private function routerWithMiddleware(): Router
    {
        $container = new Container();
        $container->instance(Container::class, $container);

        return new Router(
            new ControllerDispatcher($container),
            new MiddlewareResolver($container),
            new Pipeline(),
        );
    }

    private function routerWithValidation(): Router
    {
        $container = new Container();
        $container->instance(Container::class, $container);
        $container->singleton(Validator::class);

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

final class RouteHeaderMiddleware implements Middleware
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request)->withHeader('X-Route-Middleware', 'yes');
    }
}

final class FormRequestController
{
    /**
     * @return array<string, mixed>
     */
    public function store(StoreUserFormRequest $request): array
    {
        return $request->validated();
    }
}

final class StoreUserFormRequest extends FormRequest
{
    /**
     * @return array<string, string|list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
        ];
    }
}
