<?php

declare(strict_types=1);

namespace Atk4\Outbox\Tests;

use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Outbox;

class OutboxSeedNoAppTest extends BaseOutboxTestCase
{
    protected function getOutbox(): Outbox
    {
        $outbox = new Outbox([
            'mailer' => [FakeMailer::class],
            'model' => [Mail::class, $this->db],
        ]);
        $outbox->invokeInit();

        return $outbox;
    }
}
