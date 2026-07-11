<?php

declare(strict_types=1);

namespace WTD\Mail;

interface MailTransport
{
    public function send(MailMessage $message): void;
}
