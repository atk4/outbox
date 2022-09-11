<?php

declare(strict_types=1);

namespace Atk4\Outbox\Demos;

use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailTemplate;
use Atk4\Ui\Layout;
use Atk4\Ui\Loader;

/**
 * Example implementation of your Authenticated application.
 */
class App extends \Atk4\Ui\App
{
    public $title = 'Demo App';

    protected function init(): void
    {
        parent::init();

        $this->initLayout([Layout\Admin::class]);

        $loader = Loader::addTo($this, ['loadEvent' => false]);
        $loader->set(function (Loader $l) {
            $route = $l->getApp()->stickyGet('route');
            $route = empty($route) ? 'mail' : $route;

            switch ($route) {
                case 'mail':
                    $grid = \Atk4\Ui\Grid::addTo($l);

                    $model = new Mail($this->db);
                    $model->getField('html')->system = true;
                    $model->addExpression('time', [
                        'expr' => $model->refLink('response')->action('field', ['timestamp']),
                    ]);
                    $model->setOrder('id', 'desc');

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

                    $crud->setModel(new MailTemplate($this->db));

                    break;
            }
        });

        /** @var Layout\Admin $layout */
        $layout = $this->layout;

        $layout->menuLeft->addItem(['Mail Tracking', 'icon' => 'envelope'])->on('click', $loader->jsLoad(['route' => 'mail']));
        $layout->menuLeft->addItem(['Template Admin', 'icon' => 'cogs'])->on('click', $loader->jsLoad(['route' => 'template']));
    }
}
