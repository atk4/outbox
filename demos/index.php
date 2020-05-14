<?php

use atk4\outbox\MailAdmin;
use atk4\outbox\MailTemplateAdmin;
use atk4\outbox\Model\Mail;
use atk4\outbox\Outbox;
use atk4\outbox\Test\FakeMailer;
use atk4\ui\App;
use atk4\ui\Layout\Admin;
use atk4\ui\Loader;

include dirname(__DIR__) . '/vendor/autoload.php';
include __DIR__ . '/db.php';

$app = new App();
$app->db = $db;
$app->initLayout(\atk4\ui\Layout\Maestro::class);
$app->add([
    Outbox::class,
    [
        'mailer' => [
            FakeMailer::class,
        ],
        'model'  => [
            Mail::class,
        ],
    ],
]);

$loader = Loader::addTo($app, ['loadEvent' => false]);
$loader->set(function (Loader $l) {

    switch ($l->stickyGet('route')) {
        case 'mail':
            MailAdmin::addTo($l);
            break;
        case 'template':
            MailTemplateAdmin::addTo($l);
            break;
    }
});

/** @var Admin $layout*/
$layout = $app->layout;
$layout->menuLeft->addItem(['Mail Admin','icon' => 'envelope'])->on('click',$loader->jsLoad(['route'=>'mail']));
$layout->menuLeft->addItem(['Template Admin','icon' => 'cogs'])->on('click',$loader->jsLoad(['route'=>'template']));

$app->run();