<?php

declare(strict_types=1);

use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailTemplate;
use Atk4\Outbox\Outbox;
use Atk4\Outbox\Test\FakeMailer;
use Atk4\Ui\App;
use Atk4\Ui\Layout\Admin;
use Atk4\Ui\Loader;

include dirname(__DIR__) . '/vendor/autoload.php';

$app = new App(['title' => 'Agile Toolkit - Outbox']);
$app->db = include __DIR__ . '/db.php';
$app->initLayout([Admin::class]);
$app->add([Outbox::class, [
    'mailer' => new FakeMailer(),
    'model' => new Mail($app->db),
]]);

$loader = Loader::addTo($app);
$loader->set(function (Loader $l) {
    $route = $l->getApp()->stickyGet('route');
    $route = empty($route) ? 'mail' : $route;

    switch ($route) {
        case 'mail':
            $grid = \Atk4\Ui\Grid::addTo($l);

            $model = new Mail($l->getApp()->db);
            $model->getField('html')->system = true;
            $model->addExpression('time', $model->refLink('response')->action('field', ['timestamp']));
            $model->setOrder('id', 'DESC');

            $grid->setModel($model);

            break;
        case 'template':
            $crud = \Atk4\Ui\Crud::addTo($l, [
                'displayFields' => [
                    'identifier',
                    'subject',
                ],
                'addFields' => [
                    'identifier',
                    'subject',
                    'text',
                    'html',
                ],
                'editFields' => [
                    'identifier',
                    'subject',
                    'text',
                    'html',
                ],
            ]);

            $crud->setModel(new MailTemplate($l->getApp()->db));

            break;
    }
});

/** @var Admin $layout */
$layout = $app->layout;

$layout->menuLeft
    ->addItem(['Mail Tracking', 'icon' => 'envelope'])
    ->on('click', $loader->jsLoad(['route' => 'mail']));

$layout->menuLeft
    ->addItem(['Template Admin', 'icon' => 'cogs'])
    ->on('click', $loader->jsLoad(['route' => 'template']));

$app->run();
