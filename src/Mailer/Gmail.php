<?php

namespace atk4\outbox\Mailer;

use atk4\core\Exception;

class Gmail extends SMTP
{
    protected $host = 'smtp.gmail.com';
    protected $port = 587;
    protected $auth = true;

    public function __construct(array $defaults = [])
    {
        if (empty($defaults['password']) || empty($defaults['username'])) {
            throw new Exception('username and password must be defined in injection array');
        }

        parent::__construct($defaults);

        $this->phpmailer->isSMTP();
    }
}