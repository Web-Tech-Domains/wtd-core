<?php

declare(strict_types=1);

namespace WTD\Exception;

/**
 * Represents a route path matched with an unsupported HTTP method.
 */
final class MethodNotAllowedHttpException extends HttpException
{
    /**
     * @param list<string> $allowedMethods
     */
    public function __construct(private readonly array $allowedMethods)
    {
        parent::__construct(405, 'Method Not Allowed', [
            'Allow' => implode(', ', $allowedMethods),
        ]);
    }

    /**
     * Return the allowed methods for the matched path.
     *
     * @return list<string>
     */
    public function allowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
