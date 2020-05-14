<?php

namespace atk4\outbox\Test;

use atk4\outbox\MailerInterface;
use atk4\outbox\Model\Mail;
use atk4\outbox\Model\MailResponse;

class FakeMailer implements MailerInterface
{
    public function send(Mail $mail): MailResponse
    {
        $mail->set('status', Mail::STATUS_SENDING);
        $mail->save();

        $response = new MailResponse($mail->persistence);

        $mail->set('status', Mail::STATUS_SENT);
        $mail->save();

        return $response->save([
            'email_id' => $mail->id,
        ]);
    }
}
