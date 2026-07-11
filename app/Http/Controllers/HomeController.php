<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use WTD\Http\Request;
use WTD\Http\Response;
use WTD\View\ViewRenderer;

/**
 * Handles default application routes.
 */
final class HomeController
{
    public function __construct(private readonly ViewRenderer $views)
    {
    }

    /**
     * Render the home route.
     *
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters): Response
    {
        return Response::make($this->views->render('home', [
            'name' => 'WTD Core',
            'description' => 'A lightweight PHP 8.3 framework foundation for APIs, SaaS products, enterprise applications, and modular cloud-native projects.',
        ]));
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
