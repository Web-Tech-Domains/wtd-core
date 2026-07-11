<?php

declare(strict_types=1);

namespace Modules\Blog\Http\Controllers;

use WTD\Http\Request;
use WTD\Http\Response;
use WTD\View\ViewRenderer;

final class BlogController
{
    public function __construct(private readonly ViewRenderer $views)
    {
    }

    /**
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters): Response
    {
        return Response::make($this->views->renderModule('Blog', 'pages.index', [
            'module' => 'Blog',
        ]));
    }
}
