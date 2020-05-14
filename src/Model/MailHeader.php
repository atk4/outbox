<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailHeader extends Model
{
    public function init(): void
    {
        parent::init();
        $this->addField('name');
        $this->addField('value');
    }
}
