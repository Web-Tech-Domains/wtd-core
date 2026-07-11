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
    public function index(Request $request, array $parameters): Response
    {
        return Response::make(<<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WTD Core</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { margin: 0; background: #f7f9fc; color: #111827; }
        main { min-height: 100vh; display: grid; place-items: center; padding: 32px; }
        section { width: min(960px, 100%); }
        h1 { margin: 0 0 16px; font-size: clamp(40px, 8vw, 84px); line-height: 1; letter-spacing: 0; }
        p { max-width: 680px; margin: 0 0 28px; color: #4b5563; font-size: 18px; line-height: 1.7; }
        nav { display: flex; flex-wrap: wrap; gap: 12px; }
        a { color: #0f766e; font-weight: 700; text-decoration: none; border-bottom: 2px solid transparent; }
        a:hover { border-color: currentColor; }
    </style>
</head>
<body>
    <main>
        <section>
            <h1>WTD Core</h1>
            <p>A lightweight PHP 8.3 framework foundation for APIs, SaaS products, enterprise applications, and modular cloud-native projects.</p>
            <nav aria-label="Project links">
                <a href="/health">Health</a>
                <a href="/api/status">API Status</a>
                <a href="/docs/api">API Docs</a>
            </nav>
        </section>
    </main>
</body>
</html>
HTML);
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
