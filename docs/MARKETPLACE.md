# Marketplace

WTD Core includes a local package marketplace foundation for enterprise package discovery, installation metadata, and configuration publishing.

## Package Layout

Packages live under `packages/` by default and expose a `wtd-package.php` manifest:

```php
<?php

return [
    'name' => 'vendor/package',
    'version' => '1.0.0',
    'description' => 'Example package.',
    'providers' => [
        Vendor\Package\PackageServiceProvider::class,
    ],
    'config' => [
        'vendor-package/config.php' => 'config/vendor-package.php',
    ],
    'keywords' => ['api', 'enterprise'],
];
```

## Commands

```bash
php core marketplace:list
php core marketplace:install vendor/package
php core marketplace:publish vendor/package
```

Installed packages are recorded in `storage/framework/marketplace.php` by default. When `marketplace.auto_register` is enabled, installed package service providers are registered during application boot if their classes are available.

## Configuration

Marketplace configuration lives in `config/marketplace.php`:

```php
return [
    'paths' => [
        'packages' => 'packages',
        'installed' => 'storage/framework/marketplace.php',
    ],
    'auto_register' => true,
];
```

