<?php

declare(strict_types=1);

namespace Atk4\Outbox\Test;

use Atk4\Outbox\MailerInterface;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;

class FakeMailer implements MailerInterface
{
    public function send(Mail $message): MailResponse
    {
        $message->set('status', Mail::STATUS_SENDING);
        $message->save();

        $response = new MailResponse($message->persistence);

        $message->set('status', Mail::STATUS_SENT);
        $message->save();

        return $response->save([
            'email_id' => $message->id,
        ]);
    }
}
