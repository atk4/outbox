<?php

namespace atk4\outbox\Mailer;

class SMTP extends AbstractMailer
{
    public function __construct(array $defaults = [])
    {
        parent::__construct($defaults);
        $this->phpmailer->isSMTP();
    }
}