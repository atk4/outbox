<?php

declare(strict_types=1);

namespace Atk4\Outbox\Demos;

use Atk4\Data\Persistence;
use Atk4\Data\Persistence\Sql;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailTemplate;
use Atk4\Outbox\Outbox;
use Atk4\Outbox\Tests\FakeMailer;
use Atk4\Ui\Form\Control\Multiline;
use Atk4\Ui\Form\Control\Textarea;
use Atk4\Ui\Form\Layout\Section\Columns;
use Atk4\Ui\Form\Layout\Section\Tabs;
use Atk4\Ui\Header;
use Atk4\Ui\Layout\Admin;
use Atk4\Ui\Loader;
use Atk4\Ui\Table;

include dirname(__DIR__) . '/vendor/autoload.php';

require __DIR__ . '/init-app.php';

/** @var App $app */

$app->initLayout([Admin::class]);

Outbox::addTo($app, [
    'mailer' => new FakeMailer(),
    'model' => new Mail($app->db),
]);

class index extends \Atk4\Ui\View
{
    private Loader $loader;

    /**
     * @var Persistence|Sql
     */
    private $db;

    protected function init(): void
    {
        parent::init();

        $this->db = $this->getApp()->db;

        $this->loader = Loader::addTo($this);
        $this->loader->set(function (Loader $loader) {
            $route = $loader->getApp()->stickyGet('route');
            $route = empty($route) ? 'mail' : $route;

            switch ($route) {
                case 'tracking':
                    $this->showGridMail();

                    break;
                case 'template':
                    $this->showGridMailTemplate();

                    break;
                case 'template-edit':
                    $this->showFormMailTemplate();

                    break;
                case 'send-email':
                    $this->composeEmail();

                    break;
            }
        });

        /** @var Admin $layout */
        $layout = $this->getApp()->layout;

        $layout->menuLeft->addItem(['Mail Tracking', 'icon' => 'envelope'])->on(
            'click',
            $this->loader->jsLoad(['route' => 'tracking'])
        );
        $layout->menuLeft->addItem(['Template Grid', 'icon' => 'cogs'])->on(
            'click',
            $this->loader->jsLoad(['route' => 'template'])
        );
        $layout->menuLeft->addItem(['Template new', 'icon' => 'edit'])->on(
            'click',
            $this->loader->jsLoad(['route' => 'template-edit'])
        );
        $layout->menuLeft->addItem(['Send email', 'icon' => 'arrow right'])->on(
            'click',
            $this->loader->jsLoad(['route' => 'send-email'])
        );
    }

    public function showGridMail(): void
    {
        $grid = \Atk4\Ui\Grid::addTo($this->loader);

        $mail = new Mail($this->db);
        $mail->setOrder('id', 'desc');

        $grid->setModel($mail->ref('response'), ['subject', 'status', 'timestamp', 'code', 'message']);
    }

    public function showGridMailTemplate(): void
    {
        $grid = \Atk4\Ui\Grid::addTo($this->loader);

        $mailTemplate = new MailTemplate($this->db);
        $mailTemplate->setOnlyFields(['identifier', 'subject']);

        $grid->setModel($mailTemplate);

        $grid->addActionButton(
            'edit',
            fn ($jq, $id) => $this->loader->jsLoad(['route' => 'template-edit', 'template_id' => $id]),
            '',
            [
                'id' => $grid->jsRow()->data('id'),
            ]
        );
        $grid->addActionButton(
            'create email from template',
            fn ($jq, $id) => $this->loader->jsLoad(['route' => 'send-email', 'from_template_id' => $id]),
            '',
            [
                'id' => $grid->jsRow()->data('id'),
            ]
        );
    }

    public function showFormMailTemplate(): void
    {
        $mailTemplate = new MailTemplate($this->db);

        $title = 'Template: New MailTemplate';
        $entityTemplate = $mailTemplate->createEntity();

        $templateId = (int) $this->loader->stickyGet('template_id');

        if ($templateId > 0) {
            $entityTemplate = $mailTemplate->load($templateId);
            $title = 'Template: ' . $entityTemplate->getTitle();
        }

        Header::addTo($this->loader, [$title]);

        $form = \Atk4\Ui\Form::addTo($this->loader);

        $form->addClass('small');

        $form->setModel($entityTemplate, []);

        /** @var Columns $columns */
        $columns = $form->layout->addSubLayout([\Atk4\Ui\Form\Layout\Section\Columns::class]);
        $columns->addClass('stackable');

        $column = $columns->addColumn(8);
        $fromName = $column->addControl('from_name', [], ['neverSave' => true, 'neverPersist' => true]);
        $fromName->set($entityTemplate->ref('from')->get('name'));

        $fromEmail = $column->addControl('from_email', [], ['neverSave' => true, 'neverPersist' => true]);
        $fromEmail->set($entityTemplate->ref('from')->get('email'));

        $column->addControl('identifier', ['readOnly' => $form->model->isLoaded()]);
        $column->addControl('subject');

        /** @var Tabs $tabs */
        $tabs = $column->addSubLayout([\Atk4\Ui\Form\Layout\Section\Tabs::class]);
        $tabs->addTab('Body HTML')->addControl('html', [Textarea::class, 'rows' => 10, 'renderLabel' => false]);
        $tabs->addTab('Body Text')->addControl('text', [Textarea::class, 'rows' => 10, 'renderLabel' => false]);

        /** @var Multiline $mlTokens */
        $mlTokens = $column->addControl('_tokens', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlTokens->setModel($entityTemplate->ref('tokens'));

        $column = $columns->addColumn(8);

        /** @var Multiline $mlTo */
        $mlTo = $column->addControl('_to', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlTo->addClass('fluid');
        $mlTo->setModel($entityTemplate->ref('to'));

        /** @var Multiline $mlCc */
        $mlCc = $column->addControl('_cc', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlCc->setModel($entityTemplate->ref('cc'));

        /** @var Multiline $mlBcc */
        $mlBcc = $column->addControl('_bcc', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlBcc->setModel($entityTemplate->ref('bcc'));

        /** @var Multiline $mlReplyTo */
        $mlReplyTo = $column->addControl('_replyto', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlReplyTo->setModel($entityTemplate->ref('replyto'));

        $form->onSubmit(function (\Atk4\Ui\Form $form) use ($mlTo, $mlTokens, $mlCc, $mlBcc, $mlReplyTo) {
            $form->model->ref('from')->save([
                'name' => $form->model->get('from_name'),
                'email' => $form->model->get('from_email'),
            ]);

            $mlTo->saveRows();
            $mlTokens->saveRows();
            $mlCc->saveRows();
            $mlBcc->saveRows();
            $mlReplyTo->saveRows();

            $form->model->save();

            return $this->loader->jsReload();
        });
    }

    public function composeEmail(): void
    {
        /** @var Outbox $outbox */
        $outbox = $this->getApp()->getOutbox();
        $mail = $outbox->new();

        $title = 'New Email';

        $templateId = (int) $this->loader->stickyGet('from_template_id');

        $mailTemplate = new MailTemplate($this->db);
        $entityTemplate = $mailTemplate->createEntity();

        if ($templateId > 0) {
            $entityTemplate = $mailTemplate->load($templateId);

            $mail = $mail->withTemplate($entityTemplate);

            $title = 'New email using template: ' . $entityTemplate->getTitle();
        }

        Header::addTo($this->loader, [$title]);

        $form = \Atk4\Ui\Form::addTo($this->loader);
        $form->addClass('small');
        $form->buttonSave->set('Send Email');

        $form->setModel($mail, []);

        /** @var Columns $columns */
        $columns = $form->layout->addSubLayout([\Atk4\Ui\Form\Layout\Section\Columns::class]);
        $columns->addClass('stackable');

        $column = $columns->addColumn(8);

        $fromName = $column->addControl('from_name', [], ['neverSave' => true, 'neverPersist' => true]);
        $fromName->set($mail->ref('from')->get('name'));

        $fromEmail = $column->addControl('from_email', [], ['neverSave' => true, 'neverPersist' => true]);
        $fromEmail->set($mail->ref('from')->get('email'));

        $column->addControl('subject');

        /** @var Tabs $tabs */
        $tabs = $column->addSubLayout([\Atk4\Ui\Form\Layout\Section\Tabs::class]);
        $tabs->addTab('Body HTML')->addControl('html', [Textarea::class, 'rows' => 10, 'renderLabel' => false]);
        $tabs->addTab('Body Text')->addControl('text', [Textarea::class, 'rows' => 10, 'renderLabel' => false]);

        $tokens = $entityTemplate->get('tokens') ?? [];

        $tab = $tabs->addTab('Tokens: ' . count($tokens));

        $table = Table::addTo($tab);
        $table->setSource($tokens);

        $column = $columns->addColumn(8);

        /** @var Multiline $mlTo */
        $mlTo = $column->addControl('_to', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlTo->addClass('fluid');
        $mlTo->setModel($mail->ref('to'));

        /** @var Multiline $mlCc */
        $mlCc = $column->addControl('_cc', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlCc->setModel($mail->ref('cc'));

        /** @var Multiline $mlBcc */
        $mlBcc = $column->addControl('_bcc', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlBcc->setModel($mail->ref('bcc'));

        /** @var Multiline $mlReplyTo */
        $mlReplyTo = $column->addControl('_replyto', [Multiline::class], ['neverSave' => true, 'neverPersist' => true]);
        $mlReplyTo->setModel($mail->ref('replyto'));

        $form->onSubmit(function (\Atk4\Ui\Form $form) use ($outbox, $mlTo, $mlCc, $mlBcc, $mlReplyTo) {
            /** @var Mail $model */
            $model = $form->model;

            $model->ref('from')->save([
                'name' => $model->get('from_name'),
                'email' => $model->get('from_email'),
            ]);

            $mlTo->saveRows();
            $mlCc->saveRows();
            $mlBcc->saveRows();
            $mlReplyTo->saveRows();

            $model->save();

            $outbox->send($model);

            return [
                $this->loader->jsReload(),
                new \Atk4\Ui\JsToast('Email sent'),
            ];
        });
    }
}

index::addTo($app);
