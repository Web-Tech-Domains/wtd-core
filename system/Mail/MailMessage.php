<?php

declare(strict_types=1);

namespace WTD\Mail;

final class MailMessage
{
    /**
     * @var list<string>
     */
    private array $to = [];

    /**
     * @var list<Attachment>
     */
    private array $attachments = [];

    public function __construct(
        private string $subject = '',
        private string $body = '',
        private bool $html = false,
    ) {
    }

    public function to(string $address): self
    {
        $this->to[] = $address;

        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function text(string $body): self
    {
        $this->body = $body;
        $this->html = false;

        return $this;
    }

    public function html(string $body): self
    {
        $this->body = $body;
        $this->html = true;

        return $this;
    }

    public function attach(string $path, ?string $name = null): self
    {
        $this->attachments[] = new Attachment($path, $name);

        return $this;
    }

    public function inline(string $path, string $contentId, ?string $name = null): self
    {
        $this->attachments[] = new Attachment($path, $name, inline: true, contentId: $contentId);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function recipients(): array
    {
        return $this->to;
    }

    public function subjectLine(): string
    {
        return $this->subject;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function isHtml(): bool
    {
        return $this->html;
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        return $this->attachments;
    }
}
