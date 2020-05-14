<?php
declare(strict_types=1);

namespace atk4\outbox\Mailer;

use atk4\outbox\MailerInterface;

class Gmail extends SMTP
{
    protected $host = 'smtp.gmail.com';
    protected $port = 587;
    protected $auth = MailerInterface::SMTP_SECURE_SSL;
}
