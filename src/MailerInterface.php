<?php

declare(strict_types=1);

namespace Atk4\Outbox;

use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;

interface MailerInterface
{
    public function send(Mail $mail): MailResponse;
}
