<?php

declare(strict_types=1);

namespace Atk4\Outbox\Tests;

use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Outbox;

abstract class BaseOutboxTestCase extends GenericTestCase
{
    abstract protected function getOutbox(): Outbox;

    public function testSendCallable(): void
    {
        $this->setupDefaultDb();

        $this->getOutbox()->callableSend(function (Mail $mail) {
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

    public function testSend(): void
    {
        $this->setupDefaultDb();

        $outbox = $this->getOutbox();

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

    public function testMailSaveAsTemplate(): void
    {
        $this->setupDefaultDb();

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

    public function testWithAddress(): void
    {
        $this->setupDefaultDb();

        $outbox = $this->getOutbox();

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

    public function testWithAddressAdvanced(): void
    {
        $this->setupDefaultDb();

        $user_model = new User($this->db);
        $user_model = $user_model->loadAny();

        $outbox = $this->getOutbox();

        $mail = $outbox->new()
            ->withTemplateIdentifier('template_test_user')
            ->replaceContent('token', 'Agile Toolkit')
            ->replaceContent($user_model, 'user');

        $mail->ref('to')->insert([
            'email' => 'test@email.it',
            'name' => 'test email',
        ]);
        $mail->ref('to')->insert([
            'email' => $user_model->getMailAddress()->get('email'),
            'name' => $user_model->getMailAddress()->get('name'),
        ]);

        $mail->ref('cc')->insert([
            'email' => 'test@email.it',
            'name' => 'test email',
        ]);
        $mail->ref('cc')->insert([
            'email' => $user_model->getMailAddress()->get('email'),
            'name' => $user_model->getMailAddress()->get('name'),
        ]);
        $mail->ref('bcc')->insert([
            'email' => 'test@email.it',
            'name' => 'test email',
        ]);
        $mail->ref('bcc')->insert([
            'email' => $user_model->getMailAddress()->get('email'),
            'name' => $user_model->getMailAddress()->get('name'),
        ]);

        $mail->ref('replyto')->insert([
            'email' => 'test@email.it',
            'name' => 'test email',
        ]);
        $mail->ref('replyto')->insert([
            'email' => $user_model->getMailAddress()->get('email'),
            'name' => $user_model->getMailAddress()->get('name'),
        ]);

        $mail->ref('headers')->insert([
            'name' => 'x-custom-header',
            'value' => 'Agile Toolkit',
        ]);

        $response = $outbox->send($mail);

        $this->assertSame(
            'hi to all,<br/>this is outbox library of Agile Toolkit.<br/><br/>have a good day.<br/><br/>John Doe',
            $mail->get('html')
        );
        $this->assertSame($response->get('email_id'), $mail->id);
    }
}
