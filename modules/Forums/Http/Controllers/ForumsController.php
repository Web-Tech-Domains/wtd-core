<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use WTD\Http\Request;
use WTD\Http\Response;
use WTD\View\ViewRenderer;

final class ForumsController
{
    public function __construct(private readonly ViewRenderer $views)
    {
    }

    /**
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters): Response
    {
        $payload = [
            'stats' => [
                ['label' => 'Topics', 'value' => '128'],
                ['label' => 'Replies', 'value' => '1,482'],
                ['label' => 'Members', 'value' => '342'],
            ],
            'categories' => [
                ['name' => 'Announcements', 'count' => 12, 'tone' => 'blue'],
                ['name' => 'Framework Help', 'count' => 46, 'tone' => 'green'],
                ['name' => 'Packages', 'count' => 28, 'tone' => 'violet'],
                ['name' => 'Security', 'count' => 9, 'tone' => 'red'],
            ],
            'topics' => [
                [
                    'title' => 'How should forum modules expose package hooks?',
                    'category' => 'Packages',
                    'author' => 'Core Team',
                    'replies' => 18,
                    'views' => 312,
                    'status' => 'Open',
                    'updated' => '12 min ago',
                ],
                [
                    'title' => 'Best practices for model events and moderation logs',
                    'category' => 'Framework Help',
                    'author' => 'Maintainer',
                    'replies' => 9,
                    'views' => 144,
                    'status' => 'Answered',
                    'updated' => '1 hr ago',
                ],
                [
                    'title' => 'Proposal: changelog module release notes workflow',
                    'category' => 'Announcements',
                    'author' => 'Web Tech Domains',
                    'replies' => 24,
                    'views' => 401,
                    'status' => 'Pinned',
                    'updated' => 'Today',
                ],
            ],
            'guidelines' => [
                'Search before opening a duplicate topic.',
                'Keep titles specific and actionable.',
                'Include version, environment, and reproduction steps.',
            ],
        ];

        return Response::make($this->views->renderModule('Forums', 'pages.index', [
            'assetTags' => \vite('resources/js/modules/forums.js'),
            'forumPayload' => json_encode($payload, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
        ]));
    }
}
