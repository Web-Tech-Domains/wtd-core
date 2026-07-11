# Architecture

WTD Core is organized around small framework modules under `system/` and application code under `app/`.

## Runtime Flow

1. `public/index.php` loads Composer and the bootstrap file.
2. `bootstrap/app.php` creates the `Application` instance.
3. Service providers register framework services into the container.
4. The HTTP kernel dispatches a request through middleware, routing, and controller dispatch.
5. A `Response` is emitted to the client.

The CLI flow is similar: `core` bootstraps the application, resolves the console `Kernel`, parses input, and dispatches a command.

## Core Principles

- PSR-4 autoloading for framework and application code
- PSR-11-compatible container behavior
- PSR-3-compatible logging
- Service-provider based module registration
- Small contracts for commands, middleware, mail, notifications, queues, storage, and events
- Framework modules that can be used independently where possible

## Main Modules

- `Application`: lifecycle, service providers, health, diagnostics, maintenance mode
- `Console`: command kernel and built-in operational commands
- `Container`: bindings, singletons, scopes, tags, contextual resolution, auto-resolution
- `Http`, `Kernel`, `Routing`, `Middleware`: request lifecycle and routing
- `Validation`: validators and form request classes
- `Database`, `ORM`: query builder, schema, migrations, seeders, models, relationships
- `Auth`, `Authorization`, `Security`: identity, policies, RBAC, tokens, encryption, rate limiting, headers
- `Queue`, `Scheduler`, `Events`: background work and event-driven workflows
- `Notification`, `Mail`: delivery abstractions
- `Cache`, `Storage`, `Filesystem`: infrastructure utilities
- `CLI`: executable console runtime wrapper
- `View`: file-based template rendering
- `WebSocket`: handshake, frame, and channel foundations
