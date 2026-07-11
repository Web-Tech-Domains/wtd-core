# Hooks

WTD Core supports plugin-style hooks for application code, modules, and third-party packages.

Register hooks in `app/Hooks.php` or add more files through `config/hooks.php`.

```php
$hooks->addAction('app.booted', static function ($app): void {
    // Application is ready.
});

$hooks->addFilter('response.content', static function (string $content): string {
    return $content;
});
```

Global helpers are also available:

```php
add_action('app.before_send', static function ($response): void {
    // Inspect or log the response before output.
});

$content = apply_filters('response.content', $content);
```

Built-in hook points:

- `hooks.loaded`
- `app.booted`
- `app.request`
- `app.response`
- `app.before_send`
- `app.after_send`
- `app.before_redirect`
- `response.content`
