<?php

declare(strict_types=1);

namespace WTD\Hooks;

/**
 * Lightweight action and filter registry for application and plugin extension points.
 */
final class HookManager
{
    /**
     * @var array<string, array<int, list<callable>>>
     */
    private array $actions = [];

    /**
     * @var array<string, array<int, list<callable>>>
     */
    private array $filters = [];

    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        $this->actions[$hook][$priority][] = $callback;
    }

    public function add_action(string $hook, callable $callback, int $priority = 10): void
    {
        $this->addAction($hook, $callback, $priority);
    }

    public function doAction(string $hook, mixed ...$payload): void
    {
        foreach ($this->callbacksFor($this->actions, $hook) as $callback) {
            $callback(...$payload);
        }
    }

    public function do_action(string $hook, mixed ...$payload): void
    {
        $this->doAction($hook, ...$payload);
    }

    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        $this->filters[$hook][$priority][] = $callback;
    }

    public function add_filter(string $hook, callable $callback, int $priority = 10): void
    {
        $this->addFilter($hook, $callback, $priority);
    }

    public function applyFilters(string $hook, mixed $value, mixed ...$payload): mixed
    {
        foreach ($this->callbacksFor($this->filters, $hook) as $callback) {
            $value = $callback($value, ...$payload);
        }

        return $value;
    }

    public function apply_filters(string $hook, mixed $value, mixed ...$payload): mixed
    {
        return $this->applyFilters($hook, $value, ...$payload);
    }

    public function hasAction(string $hook): bool
    {
        return isset($this->actions[$hook]) && $this->actions[$hook] !== [];
    }

    public function hasFilter(string $hook): bool
    {
        return isset($this->filters[$hook]) && $this->filters[$hook] !== [];
    }

    public function removeAll(?string $hook = null): void
    {
        if ($hook === null) {
            $this->actions = [];
            $this->filters = [];

            return;
        }

        unset($this->actions[$hook], $this->filters[$hook]);
    }

    /**
     * @param array<string, array<int, list<callable>>> $registry
     *
     * @return list<callable>
     */
    private function callbacksFor(array $registry, string $hook): array
    {
        if (!isset($registry[$hook])) {
            return [];
        }

        $callbacksByPriority = $registry[$hook];
        ksort($callbacksByPriority);

        $callbacks = [];

        foreach ($callbacksByPriority as $priorityCallbacks) {
            array_push($callbacks, ...$priorityCallbacks);
        }

        return $callbacks;
    }
}
