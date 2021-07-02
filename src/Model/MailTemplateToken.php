<?php

declare(strict_types=1);

namespace Atk4\Outbox\Model;

use Atk4\Data\Model;

class MailTemplateToken extends Model
{
    protected function init(): void
    {
        parent::init();
        $this->addField('token');
        $this->addField('description');
    }
}
