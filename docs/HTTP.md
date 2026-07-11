# HTTP, Routing, and Middleware

HTTP features live in `system/Http`, `system/Kernel`, `system/Routing`, `system/Middleware`, `system/Cookie`, and `system/Session`.

## Routes

Define routes in `routes/web.php`.

```php
<?php

use App\Http\Controllers\HomeController;
use WTD\Routing\Router;

return static function (Router $router): void {
    $router->get('/', HomeController::class)->name('home');

    $router->group(['prefix' => '/api', 'middleware' => ['api']], static function (Router $router): void {
        $router->get('/status', static fn () => ['ok' => true]);
    });
};
```

Supported routing features include HTTP method routing, automatic `OPTIONS` responses, named routes, route groups, domain routing, API versioning, URL generation, controller dispatch, and route caching.

## Controllers

Controllers are regular classes under `app/Http/Controllers`.

```php
<?php

namespace App\Http\Controllers;

use WTD\Http\Response;

final class HomeController
{
    public function __invoke(): Response
    {
        return Response::make('Welcome to WTD Core');
    }
}
```

## Responses

The framework supports text responses, JSON responses, redirects, streamed responses, file downloads, cookies, and validation error responses.

## Middleware

Middleware implements `WTD\Middleware\Middleware` and can be assigned globally or per route through configuration and route definitions. The middleware pipeline resolves middleware through the container, so constructor dependencies are supported.

