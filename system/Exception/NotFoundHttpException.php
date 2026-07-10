<?php

declare(strict_types=1);

namespace WTD\Exception;

/**
 * Represents a missing HTTP route or resource.
 */
final class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = 'Not Found')
    {
        parent::__construct(404, $message);
    }
}
