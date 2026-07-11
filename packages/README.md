# WTD Core Packages

This directory is reserved for local WTD Core packages used by client projects, enterprise extensions, and first-party integrations.

Each package should live in its own direct child directory and expose a `wtd-package.php` manifest:

```text
packages/
  vendor-package/
    wtd-package.php
    src/
    config/
```

Minimal manifest:

```php
<?php

declare(strict_types=1);

return [
    'name' => 'vendor/package',
    'version' => '1.0.0',
    'description' => 'Package description.',
    'providers' => [
        Vendor\Package\PackageServiceProvider::class,
    ],
    'config' => [
        'vendor-package/config.php' => 'config/vendor-package.php',
    ],
    'keywords' => ['enterprise'],
];
```

Package commands:

```bash
php core marketplace:list
php core marketplace:install vendor/package
php core marketplace:publish vendor/package
```
