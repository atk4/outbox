<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailAddress extends Model
{
    public function init(): void
    {
        parent::init();
        $this->addField('email');
        $this->addField('name');
    }
}
