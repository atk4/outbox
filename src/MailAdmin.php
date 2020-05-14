<?php

namespace atk4\outbox;

use atk4\data\Model;
use atk4\outbox\Model\Mail;
use atk4\ui\CRUD;
use atk4\ui\Exception;
use atk4\ui\Grid;
use atk4\ui\View;

class MailAdmin extends Grid
{
    public function setModel(Model $m, $columns = null)
    {
        if (!is_a($m, Mail::class, true)) {
            throw new Exception(['Model must be of type Mail']);
        }

        return parent::setModel($m, $columns);
    }
}
