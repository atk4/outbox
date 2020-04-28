<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailAttachment extends Model
{
    public function init(): void
    {
        parent::init();

        $this->addField('path');
        $this->addField('name');
        $this->addField('encoding', ['default' => 'base64']);
        $this->addField('mime');
        $this->addField('disposition', ['default' => 'attachment']);
    }
}
