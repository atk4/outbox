<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailAddress extends Model
{
    public $id_field = 'email';

    public function init(): void
    {
        parent::init();

        $this->getField('email')->type = 'string';
        $this->addField('name');
    }
}
