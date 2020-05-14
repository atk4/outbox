<?php

use atk4\data\Persistence;
use atk4\outbox\MailAdmin;
use atk4\outbox\Model\Mail;
use atk4\outbox\Outbox;
use atk4\outbox\Test\Bootstrap;
use atk4\outbox\Test\FakeMailer;
use atk4\ui\App;
use atk4\ui\Layout\Admin;

include dirname(__DIR__) . '/vendor/autoload.php';
include __DIR__ . '/db.php';

$app = new App();
$app->db = $db;
$app->initLayout(Admin::class);
$app->add([
    Outbox::class,
    [
        'mailer' => [
            FakeMailer::class
        ],
        'model'  => [
            Mail::class,
        ],
    ],
]);
MailAdmin::addTo($app)->setModel(new Mail($app->db));