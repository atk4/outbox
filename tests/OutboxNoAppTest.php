<?php

namespace atk4\outbox\Test;

use atk4\core\AtkPhpunit\TestCase;
use atk4\core\Exception;
use atk4\outbox\Model\Mail;
use atk4\outbox\Outbox;

class OutboxNoAppTest extends TestCase
{
    public function test1()
    {
        $mail_model = Bootstrap::instance()->el('mail_model');

        $outbox = new Outbox([
            'mailer' => [
                FakeMailer::class,
            ],
            'model' => $mail_model,
        ]);

        $outbox->init();

        $mail = $outbox->new()
            ->withTemplateIdentifier('template_test')
            ->replaceContent('token', 'Agile Toolkit');

        $mail->ref('to')->save([
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

    public function test2()
    {
        /** @var Mail $mail_model */
        $mail_model = Bootstrap::instance()->el('mail_model');

        /** @var User $user_model */
        $user_model = Bootstrap::instance()->el('user_model');
        $user_model->loadAny();

        $outbox = new Outbox([
            'mailer' => [
                FakeMailer::class,
            ],
            'model' => $mail_model,
        ]);

        $outbox->init();

        $mail = $outbox->new()
            ->withTemplateIdentifier('template_test_user')
            ->replaceContent('token', 'Agile Toolkit')
            ->replaceContent($user_model, 'user');

        $mail->ref('to')->save([
            'email' => 'test@email.it',
            'name' => 'test email',
        ]);
        $mail->ref('to')->save($user_model->getMailAddress()->get());

        $mail->ref('cc')->save([
            'email' => 'test@email.it',
            'name' => 'test email',
        ]);
        $mail->ref('cc')->save($user_model->getMailAddress()->get());

        $mail->ref('bcc')->save([
            'email' => 'test@email.it',
            'name' => 'test email',
        ]);
        $mail->ref('bcc')->save($user_model->getMailAddress()->get());

        $mail->ref('replyto')->save([
            'email' => 'test@email.it',
            'name' => 'test email',
        ]);
        $mail->ref('replyto')->save($user_model->getMailAddress()->get());

        $mail->ref('headers')->save([
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

    public function testExceptionNoInit()
    {
        $this->expectException(Exception::class);

        /** @var Mail $mail_model */
        $mail_model = Bootstrap::instance()->el('mail_model');

        $outbox = new Outbox([
            'mailer' => [
                FakeMailer::class,
            ],
            'model' => $mail_model,
        ]);

        //$outbox->init(); <-- this cause exception on send

        $mail = $outbox->new()
            ->withTemplateIdentifier('template_test_user')
            ->replaceContent('token', 'Agile Toolkit');

        $response = $outbox->send($mail);
    }

    protected function setUp(): void
    {
        Bootstrap::instance()->setup();
    }
}
