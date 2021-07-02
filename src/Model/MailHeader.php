<?php

declare(strict_types=1);

namespace Atk4\Outbox\Model;

use Atk4\Data\Model;

class MailHeader extends Model
{
    protected function init(): void
    {
        parent::init();

        $this->addField('name');
        $this->addField('value');
    }
}
