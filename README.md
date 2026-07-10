# WTD Core

A modern, lightweight, enterprise-ready PHP framework by Web Tech Domains.

## Vision
- Lightweight
- Secure
- Modular
- PSR-compliant
- Cloud-ready

The architecture baseline is documented in [docs/SOFTWARE_ARCHITECTURE_SPECIFICATION.md](docs/SOFTWARE_ARCHITECTURE_SPECIFICATION.md).

## Initial Roadmap
1. Foundation: kernel, bootstrap, container, config, environment, CLI
2. HTTP engine: request/response, middleware, routing
3. Validation
4. Database
5. ORM
6. Authentication
7. Authorization
8. Queue
9. Scheduler
10. Cache
11. Notifications
12. Storage
13. Testing
14. Documentation

## Development

```bash
composer install
composer test
composer analyse
composer cs:fix
php core about
php core list
php core help
php core help health
php core config:cache
php core config:clear
php core migrate
php core migrate:rollback
php core db:seed
php core optimize
php core optimize:clear
php core health
php core diagnostics
```

## Current Phase

WTD Core has completed `0.1.0-alpha` foundation work, Phase 2 HTTP engine work, Phase 3 dependency injection work, Phase 4 validation work, Phase 5 database work, and Phase 6 ORM work. The current implementation includes the application lifecycle, service providers, configurable provider bootstrapping, dependency injection with transient, singleton, scoped, tagged, contextual, interface, factory, and auto-resolved services, file-based configuration loading and caching, environment loading, filesystem helpers, file logging, boot-time error handling, health reporting, persistent maintenance state, timing, memory metrics, a console kernel with parsed input, an HTTP kernel with exception rendering, 404/405 responses, full HTTP method routing, automatic OPTIONS responses, routing, route groups, domain routing, API versioning, named routes, URL generation, route caching, controller dispatch, redirect responses, file downloads, streaming responses, cookies, file-backed sessions, configurable global and route middleware, middleware pipeline, request validation helpers, form request validation classes with controller injection, nested and conditional validation rules, custom validation messages and rule extensions, HTTP 422 JSON validation error responses, PDO database foundation, query builder and grammar, database schema builder, migration runner with rollback support, database seeder runner support, database factories, query execution events, ORM models, ORM query scopes, HasOne, HasMany, BelongsTo, many-to-many, and polymorphic relationships, lifecycle events, observers, soft deletes, UUID keys, casting, accessors, mutators, and repositories.

Current PSR contract support includes PSR-4 autoloading, PSR-11 container interfaces, and PSR-3 logger interfaces.
