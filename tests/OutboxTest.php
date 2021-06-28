<?php

declare(strict_types=1);

namespace Atk4\Outbox\Test;

use Atk4\Core\AtkPhpunit\TestCase;
use Atk4\Core\Exception;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Outbox;
use Atk4\Ui\App;
use Atk4\Ui\Layout;

/**
 * Class OutboxTest.
 */
class OutboxTest extends TestCase
{
    public function testSend(): void
    {
        $outbox = $this->getOutboxFromApp();

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

    private function getApp(): App
    {
        $app = new App();
        $app->db = Bootstrap::instance()->el('persistence');
        $app->initLayout([Layout::class]);
        $app->add([
            Outbox::class,
            [
                'mailer' => [
                    FakeMailer::class,
                ],
                'model' => Mail::class,
            ],
        ]);

        return $app;
    }

    private function getOutboxFromApp(): Outbox
    {
        $app = $this->getApp();

        if (!method_exists($app, 'getOutbox')) {
            throw new Exception('App without getOutbox method');
        }

        return $app->getOutbox();
    }

    public function testSendCallable(): void
    {
        $this->getOutboxFromApp()->callableSend(function (Mail $mail) {
            $mail->withTemplateIdentifier('template_test')
                ->replaceContent('token', 'Agile Toolkit');

            $mail->ref('to')->save([
                'email' => 'destination@email.it',
                'name' => 'destination',
            ]);

            $mail->onHook('afterSend', function ($m, $response) {
                $this->assertSame(
                    'hi to all,<br/>this is outbox library of Agile Toolkit.<br/><br/>have a good day.',
                    $m->get('html')
                );
            });

            return $mail;
        });
    }

    public function testMailSaveAsTemplate(): void
    {
        /** @var Mail $mail_model */
        $mail_model = Bootstrap::instance()->_getFromCollection(
            'mail_model',
            'elements'
        );

        $template_model = $mail_model->loadAny()->saveAsTemplate('new_mail_template');
        $data = $template_model->get();
        $template_model->delete();

        foreach ($data as $fieldname => $value) {
            if ($fieldname !== $template_model->id_field && $mail_model->hasField($fieldname)) {
                $this->assertSame($value, $mail_model->get($fieldname));
            }
        }
    }

    protected function setUp(): void
    {
        Bootstrap::instance()->setup();
    }
}
