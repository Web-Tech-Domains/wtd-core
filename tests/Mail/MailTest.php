<?php

declare(strict_types=1);

namespace Tests\Mail;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Mail\MailManager;
use WTD\Mail\Mailer;
use WTD\Mail\MailMessage;
use WTD\Mail\MailServiceProvider;
use WTD\Mail\MailgunTransport;
use WTD\Mail\PostmarkTransport;
use WTD\Mail\SendGridTransport;
use WTD\Mail\SesTransport;
use WTD\Mail\SmtpTransport;
use WTD\Mail\TemplateRenderer;

final class MailTest extends TestCase
{
    public function testMailerSendsThroughNamedTransports(): void
    {
        $manager = new MailManager('smtp');
        $mailer = new Mailer($manager);
        $message = (new MailMessage())
            ->to('user@example.test')
            ->subject('Welcome')
            ->text('Hello')
            ->attach(__FILE__, 'test.php')
            ->inline(__FILE__, 'logo', 'logo.png');

        $mailer->send($message);
        $mailer->send((new MailMessage())->to('ses@example.test')->subject('SES'), 'ses');

        $smtp = $manager->transport('smtp');
        $ses = $manager->transport('ses');

        self::assertInstanceOf(SmtpTransport::class, $smtp);
        self::assertInstanceOf(SesTransport::class, $ses);
        self::assertSame('Welcome', $smtp->sent()[0]->subjectLine());
        self::assertSame('user@example.test', $smtp->sent()[0]->recipients()[0]);
        self::assertCount(2, $smtp->sent()[0]->attachments());
        self::assertTrue($smtp->sent()[0]->attachments()[1]->inline);
        self::assertSame('SES', $ses->sent()[0]->subjectLine());
        self::assertInstanceOf(MailgunTransport::class, $manager->transport('mailgun'));
        self::assertInstanceOf(PostmarkTransport::class, $manager->transport('postmark'));
        self::assertInstanceOf(SendGridTransport::class, $manager->transport('sendgrid'));
    }

    public function testTemplatesAndMarkdownRenderHtmlMessages(): void
    {
        $renderer = new TemplateRenderer();
        $mailer = new Mailer(new MailManager(), $renderer);
        $template = $mailer->template('<p>Hello {{ name }}</p>', ['name' => 'Taylor']);
        $markdown = $mailer->markdown("# Welcome\n\nHello **Taylor**");

        self::assertTrue($template->isHtml());
        self::assertSame('<p>Hello Taylor</p>', $template->body());
        self::assertStringContainsString('<h1>Welcome</h1>', $markdown->body());
        self::assertStringContainsString('<strong>Taylor</strong>', $markdown->body());
    }

    public function testMailServiceProviderRegistersMailer(): void
    {
        $basePath = dirname(__DIR__);
        self::assertNotSame('', $basePath);
        /** @var non-empty-string $basePath */

        $app = new Application($basePath, new Container(), new Repository(['mail.default' => 'sendgrid']));
        $app->register(MailServiceProvider::class);

        self::assertInstanceOf(Mailer::class, $app->container()->get(Mailer::class));
        self::assertInstanceOf(MailManager::class, $app->container()->get(MailManager::class));
        self::assertInstanceOf(TemplateRenderer::class, $app->container()->get(TemplateRenderer::class));
        self::assertInstanceOf(SendGridTransport::class, $app->container()->get(MailManager::class)->transport());
    }
}
