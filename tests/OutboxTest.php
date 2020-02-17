<?php

namespace atk4\outbox\Test;

use atk4\data\Persistence;
use atk4\outbox\Mailer\Gmail;
use atk4\outbox\Model\Mail;
use atk4\outbox\Model\MailTemplate;
use atk4\outbox\Outbox;
use atk4\ui\App;

/**
 * Class OutboxTest
 */
class OutboxTest extends \PHPUnit_Framework_TestCase
{
    public function getApp() : App {

        $app = new App();
        $app->add([
            Outbox::class,
            'mailer' => [
                Gmail::class,
                'username' => 'test',
                'password' => 'password'
            ],
            'model' => [
                Mail::class
            ]
        ]);
    }

    public function testSend()
    {
        $app = $this->getApp();

        /** @var Outbox $outbox */
        $outbox = $this->getApp()->getOutbox();

        /** @var Mail $mail */
        $mail = $outbox->new()
                       ->withTemplateIdentifier('test')
                       ->replaceContent('test','testing')
        ;

        $outbox->send($mail);
    }

    public function testSendCallable()
    {
        $app = $this->getApp();
        $user_model = new User($app->db);

        /** @var Outbox $outbox */
        $outbox = $app->getOutbox();

        /** @var Mail $mail */
        $outbox->callableSend(static function(Mail $mail) use ($user_model) {

           $mail->withTemplateIdentifier('test')
               ->replaceContent('test','testing')
               ->replaceContent([
                   'array_token_1' => 'token_content_1',
                   'array_token_2' => 'token_content_2',
               ],'testing')
                ->replaceContent( $user_model, 'user');

            return $mail;
        });
    }
}
