<?php

declare(strict_types=1);

namespace Atk4\Outbox\Test;

use Atk4\Outbox\MailerInterface;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;

class FakeMailer implements MailerInterface
{
    public function send(Mail $mail): MailResponse
    {
        $mail->set('status', Mail::STATUS_SENDING);
        $mail->save();

        $response = new MailResponse($mail->persistence);
        $entity = $response->createEntity();

        $mail->set('status', Mail::STATUS_SENT);
        $mail->save();

        return $entity->save([
            'email_id' => $mail->id,
        ]);
    }
}
