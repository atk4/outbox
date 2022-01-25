<?php

declare(strict_types=1);

namespace Atk4\Outbox\Tests;

use Atk4\Core\Exception;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Outbox;
use Atk4\Ui\App;
use Atk4\Ui\Layout;

/**
 * Class OutboxTest.
 */
class OutboxTest extends GenericTestCase
{
    public function testSend(): void
    {
        $outbox = $this->getOutboxFromApp();

        $mail = $outbox->new()
            ->withTemplateIdentifier('template_test')
            ->replaceContent('token', 'Agile Toolkit');

        $mail->ref('to')->createEntity()->save([
            'email' => 'destination@email.it',
            'name' => 'destination',
        ]);

        $response = $outbox->send($mail);

        $this->assertSame(
            'hi to all,<br/>this is outbox library of Agile Toolkit.<br/><br/>have a good day.',
            $mail->get('html')
        );
        $this->assertSame($response->get('email_id'), $mail->id);
    }

    private function getApp(): App
    {
        $app = new App([
            'always_run' => false,
            'call_exit' => false,
        ]);
        $app->initLayout([Layout::class]);

        $app->add([Outbox::class, [
            'mailer' => new FakeMailer(),
            'model' => new Mail($this->db),
        ]]);

        return $app;
    }

    private function getOutboxFromApp(): Outbox
    {
        $app = $this->getApp();

        if (!is_callable([$app, 'getOutbox'])) {
            throw new Exception('App without getOutbox method');
        }

        return $app->getOutbox();
    }

    public function testSendCallable(): void
    {
        $this->getOutboxFromApp()->callableSend(function (Mail $mail) {
            $entity = $mail->withTemplateIdentifier('template_test');
            $entity->replaceContent('token', 'Agile Toolkit');

            $entity->ref('to')->createEntity()->save([
                'email' => 'destination@email.it',
                'name' => 'destination',
            ]);

            $entity->onHook('afterSend', function ($m, $response) {
                $this->assertSame(
                    'hi to all,<br/>this is outbox library of Agile Toolkit.<br/><br/>have a good day.',
                    $m->get('html')
                );
            });

            return $entity;
        });
    }

    public function testMailSaveAsTemplate(): void
    {
        $mail_model = new Mail($this->db);

        $entity = $mail_model->createEntity();
        $entity->ref('from')->save([
            'email' => 'from@email.it',
            'name' => 'from',
        ]);

        $entity->ref('to')->import([[
            'email' => 'to1@email.it',
            'name' => 'to1',
        ]]);

        $entity->ref('cc')->import([[
            'email' => 'cc1@email.it',
            'name' => 'cc1',
        ]]);

        $entity->ref('bcc')->import([[
            'email' => 'bcc1@email.it',
            'name' => 'bcc1',
        ]]);

        $template_model = $entity->saveAsTemplate('new_mail_template');

        foreach ($template_model->getFields() as $fieldname => $field) {
            if ($fieldname === $template_model->id_field || !$entity->hasField($fieldname)) {
                continue;
            }

            $this->assertSame($template_model->get($fieldname), $entity->get($fieldname), $fieldname);
        }
    }
}
