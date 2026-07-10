<?php

declare(strict_types=1);

namespace WTD\Exception;

use Throwable;
use WTD\Config\Repository;
use WTD\Http\Response;
use WTD\Logging\Logger;
use WTD\Validation\ValidationException;

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
        if ($throwable instanceof ValidationException) {
            return Response::json([
                'message' => $throwable->getMessage(),
                'errors' => $throwable->errors(),
            ], 422);
        }

        $status = $throwable instanceof HttpException ? $throwable->statusCode() : 500;

        if ($status >= 500) {
            $this->logger->error($throwable->getMessage(), [
                'exception' => $throwable::class,
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
            ]);
        }

        if ((bool) $this->config->get('app.debug', false)) {
            $response = Response::json([
                'error' => $throwable->getMessage(),
                'exception' => $throwable::class,
            ], $status);

            return $this->withExceptionHeaders($response, $throwable);
        }

        return $this->withExceptionHeaders(Response::make($this->message($status), $status), $throwable);
    }

    /**
     * Return a safe default message for a status code.
     */
    private function message(int $status): string
    {
        return match ($status) {
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            default => 'Server Error',
        };
    }

    /**
     * Attach exception headers to the response.
     */
    private function withExceptionHeaders(Response $response, Throwable $throwable): Response
    {
        if (!$throwable instanceof HttpException) {
            return $response;
        }

        foreach ($throwable->headers() as $name => $value) {
            $response->withHeader($name, $value);
        }

        return $response;
    }
}
