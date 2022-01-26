<?php

declare(strict_types=1);

namespace Atk4\Outbox\Mailer;

use PHPMailer\PHPMailer\PHPMailer;

class Gmail extends Smtp
{
    protected $host = 'smtp.gmail.com';
    protected $port = 587;
    protected $auth = true;
    protected $secure = PHPMailer::ENCRYPTION_SMTPS;
}
