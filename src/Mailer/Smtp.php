<?php

declare(strict_types=1);

namespace Atk4\Outbox\Mailer;

use PHPMailer\PHPMailer\SMTP as PHPMailerSMTP;

class Smtp extends AbstractMailer
{
    /** @var int */
    protected $debug = PHPMailerSMTP::DEBUG_OFF;

    /** @var bool */
    protected $auth = false;

    /** @var string */
    protected $host = 'localhost';

    /** @var int */
    protected $port = 587;

    /** @var string */
    protected $secure = '';

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    public function __construct(array $defaults = [])
    {
        parent::__construct($defaults);

        $this->phpmailer->isSMTP();

        $this->phpmailer->SMTPDebug = $this->debug;

        $this->phpmailer->Host = $this->host;
        $this->phpmailer->Port = $this->port;
        $this->phpmailer->SMTPSecure = $this->secure;

        $this->phpmailer->SMTPAuth = $this->auth;
        $this->phpmailer->Username = $this->username;
        $this->phpmailer->Password = $this->password;
    }
}
