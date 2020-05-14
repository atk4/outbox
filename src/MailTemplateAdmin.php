<?php

namespace atk4\outbox;

use atk4\outbox\Model\Mail;
use atk4\outbox\Model\MailTemplate;
use atk4\ui\CRUD;
use atk4\ui\View;

class MailTemplateAdmin extends CRUD
{
    public function init(): void
    {
        parent::init();

        $this->setModel(new MailTemplate($this->app->db));
    }
}