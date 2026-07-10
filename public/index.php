<?php

declare(strict_types=1);

use WTD\Application\Application;
use WTD\Http\Request;
use WTD\Kernel\HttpKernel;

/** @var Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

/** @var HttpKernel $kernel */
$kernel = $app->container()->get(HttpKernel::class);
$kernel->handle(Request::capture())->send();
