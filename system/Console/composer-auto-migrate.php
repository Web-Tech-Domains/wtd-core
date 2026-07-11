<?php

declare(strict_types=1);

use WTD\Console\ComposerAutoMigrator;

$basePath = dirname(__DIR__, 2);

require_once $basePath . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

exit((new ComposerAutoMigrator($basePath))->run());
