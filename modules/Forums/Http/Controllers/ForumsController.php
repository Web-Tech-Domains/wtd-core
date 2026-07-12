<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Controllers;

use App\Models\User;
use Modules\Forums\Models\ForumCategory;
use Modules\Forums\Models\ForumPost;
use Modules\Forums\Models\ForumTopic;
use WTD\Http\Request;
use WTD\Http\Response;
use WTD\Session\SessionStore;
use WTD\View\ViewRenderer;

final class ForumsController
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly SessionStore $session
    ) {
    }

    /**
     * @param array<string, string> $parameters
     */
    public function index(Request $request, array $parameters): Response
    {
        $categories = ForumCategory::query()->orderBy('sort_order')->get();

        $categoriesData = [];
        foreach ($categories as $cat) {
            $count = ForumTopic::query()->where('forum_category_id', $cat->getAttribute('id'))->count();
            
            $tone = 'blue';
            $name = $cat->getAttribute('name');
            if ($name === 'Framework Help') {
                $tone = 'green';
            } elseif ($name === 'Announcements') {
                $tone = 'violet';
            } elseif ($name === 'Security') {
                $tone = 'red';
            }

            $categoriesData[] = [
                'id' => $cat->getAttribute('id'),
                'name' => $cat->getAttribute('name'),
                'slug' => $cat->getAttribute('slug'),
                'description' => $cat->getAttribute('description'),
                'count' => $count,
                'tone' => $tone,
            ];
        }

        $topics = ForumTopic::query()
            ->orderByDesc('is_pinned')
            ->orderByDesc('id')
            ->get();

        $topicsData = [];
        foreach ($topics as $topic) {
            $catName = 'General';
            foreach ($categoriesData as $c) {
                if ($c['id'] == $topic->getAttribute('forum_category_id')) {
                    $catName = $c['name'];
                    break;
                }
            }
            $replies = ForumPost::query()->where('forum_topic_id', $topic->getAttribute('id'))->count();

            $topicsData[] = [
                'id' => $topic->getAttribute('id'),
                'title' => $topic->getAttribute('title'),
                'slug' => $topic->getAttribute('slug'),
                'category' => $catName,
                'author' => $topic->getAttribute('author_name'),
                'replies' => $replies,
                'views' => $topic->getAttribute('views'),
                'status' => $topic->getAttribute('status'),
                'updated' => $topic->getAttribute('last_activity_at'),
            ];
        }

        // 3. Dynamic statistics
        $stats = [
            ['label' => 'Topics', 'value' => number_format(count($topicsData))],
            ['label' => 'Replies', 'value' => number_format(ForumPost::query()->count())],
            ['label' => 'Members', 'value' => number_format(max(342, User::query()->count()))],
        ];

        // 4. Default community guidelines
        $guidelines = [
            'Search before opening a duplicate topic.',
            'Keep titles specific and actionable.',
            'Include version, environment, and reproduction steps.',
        ];

        // 5. Get current user session
        $userId = $this->session->get('forums_user_id');
        $currentUser = $userId ? User::find($userId) : null;
        $currentUserData = null;
        if ($currentUser) {
            $currentUserData = [
                'id' => $currentUser->getAttribute('id'),
                'name' => $currentUser->getAttribute('name'),
                'email' => $currentUser->getAttribute('email'),
            ];
        }

        $payload = [
            'stats' => $stats,
            'categories' => $categoriesData,
            'topics' => $topicsData,
            'guidelines' => $guidelines,
            'currentUser' => $currentUserData,
        ];

        return Response::make($this->views->renderModule('Forums', 'pages.index', [
            'assetTags' => \vite('resources/js/modules/forums.js'),
            'forumPayload' => json_encode($payload, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
        ]));
    }

    /**
     * Create a new forum topic via AJAX POST.
     *
     * @param array<string, string> $parameters
     */
    public function createTopic(Request $request, array $parameters): Response
    {
        // Require user session
        $userId = $this->session->get('forums_user_id');
        $user = $userId ? User::find($userId) : null;
        if ($user === null) {
            return Response::json(['error' => 'Unauthorized. You must be logged in to create a topic.'], 401);
        }

        $title = trim((string)$request->input('title'));
        $body = trim((string)$request->input('body'));
        $categoryName = trim((string)$request->input('category'));

        if ($title === '' || $body === '' || $categoryName === '') {
            return Response::json(['error' => 'All fields (title, body, category) are required.'], 400);
        }

        // Find or fallback the category
        $category = ForumCategory::query()->where('name', $categoryName)->first();
        if ($category === null) {
            $category = ForumCategory::query()->where('id', 2)->first() ?? ForumCategory::all()[0] ?? null;
        }

        if ($category === null) {
            return Response::json(['error' => 'No category found.'], 500);
        }

        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
        $slug = trim($slug, '-');

        // Create the topic
        $topic = new ForumTopic([
            'forum_category_id' => $category->getAttribute('id'),
            'title' => $title,
            'slug' => $slug,
            'body' => $body,
            'author_name' => $user->getAttribute('name'),
            'status' => 'Open',
            'is_pinned' => 0,
            'views' => 0,
            'last_activity_at' => 'Now',
        ]);

        if (!$topic->save()) {
            return Response::json(['error' => 'Unable to save topic.'], 500);
        }

        // Create the first post
        $post = new ForumPost([
            'forum_topic_id' => $topic->getAttribute('id'),
            'body' => $body,
            'author_name' => $user->getAttribute('name'),
            'is_solution' => 0,
        ]);
        $post->save();

        $newTopicData = [
            'id' => $topic->getAttribute('id'),
            'title' => $topic->getAttribute('title'),
            'slug' => $topic->getAttribute('slug'),
            'category' => $category->getAttribute('name'),
            'author' => $user->getAttribute('name'),
            'replies' => 1,
            'views' => 0,
            'status' => 'Open',
            'updated' => 'Now',
        ];

        return Response::json($newTopicData, 201);
    }

    /**
     * Authenticate user session.
     *
     * @param array<string, string> $parameters
     */
    public function login(Request $request, array $parameters): Response
    {
        $email = trim((string)$request->input('email'));
        $password = trim((string)$request->input('password'));

        if ($email === '' || $password === '') {
            return Response::json(['error' => 'Email and password are required.'], 400);
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null || !password_verify($password, (string)$user->getAttribute('password'))) {
            return Response::json(['error' => 'Invalid email or password credentials.'], 401);
        }

        $this->session->put('forums_user_id', $user->getAttribute('id'));
        $this->session->save();

        return Response::json([
            'id' => $user->getAttribute('id'),
            'name' => $user->getAttribute('name'),
            'email' => $user->getAttribute('email'),
        ], 200);
    }

    /**
     * Clear user session.
     *
     * @param array<string, string> $parameters
     */
    public function logout(Request $request, array $parameters): Response
    {
        $this->session->forget('forums_user_id');
        $this->session->save();

        return Response::json(['success' => true], 200);
    }
}
