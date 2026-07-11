<?php

declare(strict_types=1);

namespace WTD\Mail;

use WTD\Support\ServiceProvider;

final class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(TemplateRenderer::class, fn (): TemplateRenderer => new TemplateRenderer());
        $this->container()->singleton(
            MailManager::class,
            fn (): MailManager => new MailManager((string) $this->app->config()->get('mail.default', 'smtp')),
        );
        $this->container()->singleton(
            Mailer::class,
            fn (): Mailer => new Mailer(
                $this->container()->get(MailManager::class),
                $this->container()->get(TemplateRenderer::class),
            ),
        );
    }
}
