<?php


namespace Atk4\Outbox\Mailer;


class Sendmail extends AbstractMailer
{
    public function __construct(array $defaults = [])
    {
        parent::__construct($defaults);

        $this->phpmailer->isSendmail();
    }
}