# Developer Experience

WTD Core includes opt-in developer tooling for local debugging, API documentation, code generation, benchmarks, IDE metadata, and readable error pages.

## Configuration

Developer tooling is configured in `config/developer.php`.

```php
return [
    'enabled' => true,
    'debug_toolbar' => true,
    'api_docs' => true,
    'error_pages' => true,
    'benchmark_iterations' => 100,
];
```

HTTP-facing tools should stay disabled in production unless they are protected by deployment-specific access controls.

## Debug Toolbar And Profiler

`WTD\DeveloperExperience\DebugToolbarMiddleware` appends a compact toolbar to HTML responses when `developer.enabled` and `developer.debug_toolbar` are both enabled.

The toolbar uses `WTD\DeveloperExperience\Profiler` to expose elapsed time, peak memory, response status, and request path.

## API Documentation And OpenAPI

When `developer.api_docs` is enabled, the developer experience provider exposes:

- `/docs/api`
- `/docs/openapi.json`

OpenAPI output can also be generated from the CLI:

```bash
php core api:docs
```

## Code Generator

The API resource generator creates a controller and prints route snippets:

```bash
php core make:resource Post --model=Post
```

## Benchmark Tool

The benchmark command runs a path through the HTTP kernel and reports JSON timing data:

```bash
php core benchmark / --iterations=100
```

## IDE Helper

Generate framework IDE helper stubs with:

```bash
php core ide:helper
```

## Error Pages

When `developer.error_pages` is enabled, framework exceptions can render readable HTML error pages. Debug mode includes exception details; non-debug mode shows safe messages.

