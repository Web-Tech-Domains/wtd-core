<?php

declare(strict_types=1);

namespace WTD\Exception;

use Throwable;
use WTD\Config\Repository;
use WTD\Http\Response;
use WTD\Logging\Logger;

/**
 * Converts HTTP exceptions into responses.
 */
final class ExceptionRenderer
{
    public function __construct(
        private readonly Repository $config,
        private readonly Logger $logger,
    ) {
    }

    /**
     * Render a throwable to an HTTP response.
     */
    public function render(Throwable $throwable): Response
    {
        $status = $throwable instanceof HttpException ? $throwable->statusCode() : 500;

        if ($status >= 500) {
            $this->logger->error($throwable->getMessage(), [
                'exception' => $throwable::class,
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
            ]);
        }

        if ((bool) $this->config->get('app.debug', false)) {
            return Response::json([
                'error' => $throwable->getMessage(),
                'exception' => $throwable::class,
            ], $status);
        }

        return Response::make($this->message($status), $status);
    }

    /**
     * Return a safe default message for a status code.
     */
    private function message(int $status): string
    {
        return match ($status) {
            404 => 'Not Found',
            default => 'Server Error',
        };
    }
}
