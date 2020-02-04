<?php

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailResponse extends Model
{
    public $table = 'mail_response';

    public function init()
    {
        parent::init();

        $this->hasOne("email_id", Mail::class);

        $this->addField("code", ['type' => 'int']);
        $this->addField("message", ['type' => 'string']);

        $this->addField("timestamp", ['type' => 'datetime', 'default' => new \DateTime()]);
    }
}