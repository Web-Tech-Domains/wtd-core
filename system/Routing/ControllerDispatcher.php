<?php

declare(strict_types=1);

namespace WTD\Routing;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use WTD\Container\Container;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Validation\FormRequest;
use WTD\Validation\Validator;

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
            return $this->toResponse($action(...$this->arguments(new ReflectionFunction($action), $request, $parameters)));
        }

        if (is_string($action)) {
            $controller = $this->container->get($action);

            if (!is_object($controller) || !is_callable($controller)) {
                throw new InvalidArgumentException(sprintf('Controller [%s] is not invokable.', $action));
            }

            return $this->toResponse($controller(...$this->arguments(new ReflectionMethod($controller, '__invoke'), $request, $parameters)));
        }

        [$class, $method] = $action;
        $controller = $this->container->get($class);

        return $this->toResponse($controller->{$method}(...$this->arguments(new ReflectionMethod($controller, $method), $request, $parameters)));
    }

    /**
     * Build route action arguments from parameter types.
     *
     * @param array<string, string> $parameters
     *
     * @return list<mixed>
     */
    private function arguments(ReflectionFunctionAbstract $reflection, Request $request, array $parameters): array
    {
        $arguments = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $typeName = $type->getName();

                if ($typeName === Request::class) {
                    $arguments[] = $request;
                    continue;
                }

                if (is_subclass_of($typeName, FormRequest::class)) {
                    /** @var class-string<FormRequest> $typeName */
                    $arguments[] = $typeName::from($request, $this->container->get(Validator::class));
                    continue;
                }
            }

            if ($type instanceof ReflectionNamedType && $type->getName() === 'array') {
                $arguments[] = $parameters;
                continue;
            }

            if (array_key_exists($parameter->getName(), $parameters)) {
                $arguments[] = $parameters[$parameter->getName()];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
            }
        }

        return $arguments;
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
