<?php

declare(strict_types=1);

namespace Atk4\Outbox;

use Atk4\Outbox\Model\Mail;
use Atk4\Ui\Grid;

class MailAdmin extends Grid
{
    protected function init(): void
    {
        parent::init();

        $app = $this->getApp();

        $model = new Mail($app->db);
        $model->getField('html')->system = true;
        $model->addExpression('time', $model->refLink('response')->action('field', ['timestamp']));
        $model->setOrder('id', 'DESC');
        $this->setModel($model);
    }
}
