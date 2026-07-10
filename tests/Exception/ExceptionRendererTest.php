<?php

declare(strict_types=1);

namespace Tests\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WTD\Config\Repository;
use WTD\Exception\ExceptionRenderer;
use WTD\Exception\NotFoundHttpException;
use WTD\Logging\Logger;

final class ExceptionRendererTest extends TestCase
{
    public function testRendererRendersHttpExceptions(): void
    {
        $response = $this->renderer(false)->render(new NotFoundHttpException());

        self::assertSame(404, $response->status());
        self::assertSame('Not Found', $response->content());
    }

    public function testRendererCanExposeDebugDetails(): void
    {
        $response = $this->renderer(true)->render(new RuntimeException('Detailed failure'));

        self::assertSame(500, $response->status());
        self::assertStringContainsString('Detailed failure', $response->content());
    }

    private function renderer(bool $debug): ExceptionRenderer
    {
        return new ExceptionRenderer(
            new Repository(['app.debug' => $debug]),
            new Logger(dirname(__DIR__) . '/tmp/logs/exceptions.log'),
        );
    }
}
