<?php

declare(strict_types=1);

namespace Atk4\Outbox;

use Atk4\Outbox\Model\MailTemplate;
use Atk4\Ui\Crud;

class MailTemplateAdmin extends Crud
{
    public $displayFields = [
        'identifier',
        'subject',
    ];

    public $addFields = [
        'identifier',
        'subject',
        'text',
        'html',
    ];

    public $editFields = [
        'identifier',
        'subject',
        'text',
        'html',
    ];

    protected function init(): void
    {
        parent::init();

        $this->setModel(new MailTemplate($this->getApp()->db));
    }
}
