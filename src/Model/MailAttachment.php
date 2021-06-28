<?php

declare(strict_types=1);

namespace Atk4\Outbox\Model;

use Atk4\Data\Model;

class MailAttachment extends Model
{
    protected function init(): void
    {
        parent::init();

        $this->addField('path');
        $this->addField('name');
        $this->addField('encoding', ['default' => 'base64']);
        $this->addField('mime');
        $this->addField('disposition', ['default' => 'attachment']);
    }
}
