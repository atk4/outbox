<?php

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailTemplateToken extends Model
{
    public function init()
    {
        parent::init();

        $this->addField('identifier');
        $this->addField('description');
    }
}