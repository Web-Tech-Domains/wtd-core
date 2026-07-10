# WTD Core

A modern, lightweight, enterprise-ready PHP framework by Web Tech Domains.

## Vision
- Lightweight
- Secure
- Modular
- PSR-compliant
- Cloud-ready

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
php core health
php core diagnostics
```

## Current Phase

WTD Core is in `0.1.0-alpha` foundation work. The current implementation includes the application lifecycle, service providers, dependency injection, file-based configuration loading, environment loading, filesystem helpers, file logging, error handling, health reporting, persistent maintenance state, timing, memory metrics, and a console kernel with built-in CLI commands.

Current PSR contract support includes PSR-4 autoloading, PSR-11 container interfaces, and PSR-3 logger interfaces.
