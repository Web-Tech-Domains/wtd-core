<?php

declare(strict_types=1);

namespace WTD\Mail;

final class Mailer
{
    public function __construct(
        private readonly MailManager $manager,
        private readonly TemplateRenderer $renderer = new TemplateRenderer(),
    ) {
    }

    public function send(MailMessage $message, ?string $transport = null): void
    {
        $this->manager->send($message, $transport);
    }

    /**
     * @param array<string, scalar|null> $data
     */
    public function template(string $template, array $data = []): MailMessage
    {
        return (new MailMessage())->html($this->renderer->render($template, $data));
    }

    /**
     * @param array<string, scalar|null> $data
     */
    public function markdown(string $markdown, array $data = []): MailMessage
    {
        return (new MailMessage())->html($this->renderer->markdown($markdown, $data));
    }
}
