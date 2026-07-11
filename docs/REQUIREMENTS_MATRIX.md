# Requirements Matrix

This matrix maps the architecture specification to implemented project assets.

## Platform And Tooling

| Requirement | Status |
| --- | --- |
| PHP 8.3+ | `composer.json`, CI PHP 8.3 |
| Composer | `composer.json` |
| MySQL, MariaDB, PostgreSQL, SQLite | PDO database manager and connection config |
| Redis | Cache and queue drivers |
| RabbitMQ | Queue driver and Docker Compose service |
| Docker | `Dockerfile`, `docker-compose.yml` |
| Nginx | `docker/nginx/default.conf` |
| Apache | `docker/apache/vhost.conf`, `public/.htaccess` |
| PHP-FPM | `Dockerfile`, `docker/php-fpm/wtd.ini` |
| OpenAPI | Developer experience OpenAPI generator |
| JWT | Authentication JWT service |
| OAuth2 | OAuth state protection foundation |
| PHPUnit | `phpunit.xml.dist`, tests |
| PHPStan | `phpstan.neon`, `composer analyse` |
| PHP-CS-Fixer | `.php-cs-fixer.dist.php` |
| Rector | `rector.php` |
| GitHub Actions | `.github/workflows/ci.yml` |

## Standards

| Requirement | Status |
| --- | --- |
| PSR-1, PSR-12 | Enforced by PHP-CS-Fixer |
| PSR-4 | Composer autoload |
| PSR-11 | Container implements `Psr\Container\ContainerInterface` |
| PSR-3 | Logger implements `Psr\Log\LoggerInterface` |
| PSR-7, PSR-15, PSR-17, PSR-18 | HTTP abstractions and documented compatibility targets |
| PSR-14 | Event dispatcher foundation |
| Semantic Versioning | `Application::VERSION` |
| Conventional Commits, Git Flow | Project process requirements documented by the spec |
| SOLID, DRY, KISS, YAGNI, Clean Architecture | Modular service-provider architecture and focused tests |
| DDD and Hexagonal compatibility | Package, service provider, repository, event, and module boundaries |

## Product Roadmap

| Area | Status |
| --- | --- |
| Foundation, HTTP, DI, validation, database, ORM | Implemented and tested |
| Auth, authorization, security | Implemented and tested |
| Queue, scheduler, events | Implemented and tested |
| Notifications, mail, cache, storage | Implemented and tested |
| CLI runtime, views, WebSocket foundations | Implemented and tested |
| CLI, documentation, developer experience | Implemented and tested |
| Marketplace, multi-tenancy, AI integrations, monitoring/admin | Implemented and tested |
