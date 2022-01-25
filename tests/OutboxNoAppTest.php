<?php

declare(strict_types=1);

namespace Atk4\Outbox\Test;

use Atk4\Core\Phpunit\TestCase;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Outbox;

class OutboxNoAppTest extends TestCase
{
    public function testWithAddress(): void
    {
        $mail_model = Bootstrap::instance()->el('mail_model');

        $outbox = new Outbox([
            'mailer' => new FakeMailer(),
            'model' => $mail_model,
        ]);
        $outbox->invokeInit();

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
        /** @var Mail $mail_model */
        $mail_model = Bootstrap::instance()->el('mail_model');

        /** @var User $user_model */
        $user_model = Bootstrap::instance()->el('user_model');
        $user_model = $user_model->loadAny();

        $outbox = new Outbox([
            'mailer' => new FakeMailer(),
            'model' => $mail_model,
        ]);
        $outbox->invokeInit();

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

    protected function setUp(): void
    {
        Bootstrap::instance()->setup();
    }
}
