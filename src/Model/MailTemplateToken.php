<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailTemplateToken extends Model
{
    public $id_field = 'token';

    public function init(): void
    {
        parent::init();
        $this->getField('token')->type = 'string';
        $this->addField('description');
    }
}
