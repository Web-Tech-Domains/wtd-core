# Enterprise

WTD Core enterprise foundations include marketplace packages, multi-tenancy, AI package integrations, and monitoring/administration.

## Multi-Tenancy

Tenancy is configured in `config/tenancy.php`.

```php
return [
    'enabled' => true,
    'header' => 'X-Tenant',
    'default' => 'acme',
    'tenants' => [
        'acme' => ['name' => 'Acme Inc.'],
    ],
];
```

`WTD\Tenancy\TenantMiddleware` resolves the current tenant from the configured header or default tenant.

```bash
php core tenant:list
```

## AI Package Integrations

AI integrations are exposed through `WTD\AI\AiManager`. Packages can register additional providers by calling `extend()`.

```bash
php core ai:providers
```

The built-in `null` provider is deterministic and safe for local testing.

## Monitoring And Administration

Monitoring services expose runtime diagnostics and custom metrics through `WTD\Monitoring\SystemMonitor`.

```bash
php core monitor:report
```

When `monitoring.admin_enabled` is enabled, the framework registers a small HTML admin system report route at `monitoring.admin_path`.

