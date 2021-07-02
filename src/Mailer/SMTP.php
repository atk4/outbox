<?php

declare(strict_types=1);

namespace Atk4\Outbox\Mailer;

class SMTP extends AbstractMailer
{
    public function __construct(array $defaults = [])
    {
        parent::__construct($defaults);
        $this->phpmailer->isSMTP();
    }
}
