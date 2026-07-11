# Getting Started

WTD Core is a lightweight PHP 8.3+ framework core for APIs, SaaS products, and enterprise applications.

## Requirements

- PHP 8.3 or newer
- Composer
- PDO extension for database-backed features

## Install Dependencies

```bash
composer install
```

## Run Quality Checks

```bash
composer test
composer analyse
vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no
```

## Application Entry Points

- HTTP entry point: `public/index.php`
- CLI entry point: `core`
- Bootstrap file: `bootstrap/app.php`
- Web routes: `routes/web.php`
- Configuration: `config/*.php`

## Define A Route

```php
<?php

use WTD\Http\Response;
use WTD\Routing\Router;

return static function (Router $router): void {
    $router->get('/health', static fn (): Response => Response::json(['ok' => true]));
};
```

## Generate Application Code

```bash
php core make:controller HomeController
php core make:model User
php core make:command SyncCommand --command=app:sync
```

## Create A Minimal Project Skeleton

```bash
php core app:new demo
```

