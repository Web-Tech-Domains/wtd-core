<?php

declare(strict_types=1);

use WTD\Hooks\HookManager;

/** @var HookManager $hooks */

/*
|--------------------------------------------------------------------------
| Application Hooks
|--------------------------------------------------------------------------
|
| Register lightweight extension points for plugins, modules, and application
| customizations. Use actions for side effects and filters to transform values.
|
| Examples:
|
| $hooks->addAction('app.booted', static function (): void {
|     // Run after service providers have booted.
| });
|
| $hooks->addFilter('response.content', static function (string $content): string {
|     return $content;
| });
|
*/
