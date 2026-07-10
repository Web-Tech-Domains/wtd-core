<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use WTD\Http\Request;
use WTD\Http\Response;

/**
 * Handles default application routes.
 */
final class HomeController
{
    /**
     * Render the home route.
     *
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters): string
    {
        return 'Welcome to WTD Core';
    }

    /**
     * Render the health route.
     *
     * @param array<string, string> $parameters
     *
     * @return array<string, string>
     */
    public function health(Request $request, array $parameters): array
    {
        return ['status' => 'ok'];
    }

    /**
     * Render the API status route.
     *
     * @param array<string, string> $parameters
     *
     * @return array<string, string>
     */
    public function apiStatus(Request $request, array $parameters): array
    {
        return [
            'status' => 'ok',
            'scope' => 'api',
        ];
    }

    /**
     * Render a streamed response.
     *
     * @param array<string, string> $parameters
     */
    public function stream(Request $request, array $parameters): Response
    {
        return Response::stream(static fn (): string => 'streamed response');
    }

    /**
     * Render a sample file download response.
     *
     * @param array<string, string> $parameters
     */
    public function download(Request $request, array $parameters): Response
    {
        return Response::download(dirname(__DIR__, 3) . '/README.md', 'wtd-core-readme.md');
    }
}
