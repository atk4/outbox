<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;
use DateTime;

class MailResponse extends Model
{
    public $table = 'mail_response';

    public function init(): void
    {
        parent::init();

        $this->hasOne("email_id", Mail::class);

        $this->addField("code", ['type' => 'int', 'default' => 0]);
        $this->addField("message", ['type' => 'string']);

        $this->addField("timestamp", ['type' => 'datetime', 'default' => new DateTime()]);
    }
}
