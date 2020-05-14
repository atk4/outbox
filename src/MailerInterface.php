<?php

declare(strict_types=1);

namespace atk4\outbox;

use atk4\outbox\Model\Mail;
use atk4\outbox\Model\MailResponse;

interface MailerInterface
{
    const SMTP_SECURE_NULL = '';
    const SMTP_SECURE_TLS  = 'tls';
    const SMTP_SECURE_SSL  = 'ssl';

    public function send(Mail $Message): MailResponse;
}
