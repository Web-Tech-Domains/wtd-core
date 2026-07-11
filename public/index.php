<?php

declare(strict_types=1);

use WTD\Application\Application;
use WTD\Http\Request;
use WTD\Kernel\HttpKernel;

/** @var Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

/** @var HttpKernel $kernel */
$kernel = $app->container()->get(HttpKernel::class);
$request = Request::capture();
do_action('app.request', $request);

$response = $kernel->handle($request);
do_action('app.response', $response, $request);
do_action('app.before_send', $response, $request);

$response->send();
do_action('app.after_send', $response, $request);
