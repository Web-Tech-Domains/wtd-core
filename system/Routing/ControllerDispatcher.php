<?php

declare(strict_types=1);

namespace WTD\Routing;

use Closure;
use InvalidArgumentException;
use WTD\Container\Container;
use WTD\Http\Request;
use WTD\Http\Response;

/**
 * Executes route actions and normalizes return values to responses.
 */
final class ControllerDispatcher
{
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * Dispatch a route action.
     *
     * @param Closure(Request, array<string, string>): (Response|string|array<string, mixed>)|class-string|array{0: class-string, 1: non-empty-string} $action
     * @param array<string, string> $parameters
     */
    public function dispatch(Closure|array|string $action, Request $request, array $parameters): Response
    {
        if ($action instanceof Closure) {
            return $this->toResponse($action($request, $parameters));
        }

        if (is_string($action)) {
            $controller = $this->container->get($action);

            if (!is_callable($controller)) {
                throw new InvalidArgumentException(sprintf('Controller [%s] is not invokable.', $action));
            }

            return $this->toResponse($controller($request, $parameters));
        }

        [$class, $method] = $action;
        $controller = $this->container->get($class);

        return $this->toResponse($controller->{$method}($request, $parameters));
    }

    /**
     * Normalize route action results.
     *
     * @param Response|string|array<string, mixed> $result
     */
    private function toResponse(Response|string|array $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::make($result);
    }
}
