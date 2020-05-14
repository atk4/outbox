<?php

namespace atk4\outbox;

use atk4\outbox\Model\Mail;
use atk4\ui\Grid;

class MailAdmin extends Grid
{
    public function init(): void
    {
        parent::init();

        $this->setModel(new Mail($this->app->db));
    }
}
