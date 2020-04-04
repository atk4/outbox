<?php
declare(strict_types=1);

namespace atk4\outbox;

use atk4\outbox\Model\Mail;

interface MailerInterface
{
    public function send(Mail $Message): void;
}
