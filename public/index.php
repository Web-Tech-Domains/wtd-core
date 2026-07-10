<?php

declare(strict_types=1);

use WTD\Application\Application;

/** @var Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

echo 'Welcome to ' . $app->name();
