<?php

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailAddress extends Model
{
    public function init()
    {
        parent::init();
        $this->addField('email');
        $this->addField('name');
    }
}