<?php

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailAttachment extends Model
{
    public function init()
    {
        parent::init();

        $this->addField('path');
        $this->addField('name');
        $this->addField('encoding', ['default' => 'base64']);
        $this->addField('mime');
        $this->addField('disposition', ['default' => 'attachment']);
    }
}