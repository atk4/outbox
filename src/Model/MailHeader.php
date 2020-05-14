<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailHeader extends Model
{
    public $id_field = 'name';

    public function init(): void
    {
        parent::init();
        $this->getField('name')->type = 'string';
        $this->addField('value');
    }
}
