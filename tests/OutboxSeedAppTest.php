<?php

declare(strict_types=1);

namespace Atk4\Outbox\Tests;

use Atk4\Core\Exception;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Outbox;
use Atk4\Ui\App;
use Atk4\Ui\Layout;

class OutboxSeedAppTest extends BaseOutboxTestCase
{
    protected function getOutbox(): Outbox
    {
        $app = new App([
            'always_run' => false,
            'call_exit' => false,
        ]);
        $app->initLayout([Layout::class]);

        Outbox::addTo($app, [
            'mailer' => [FakeMailer::class],
            'model' => [Mail::class, $this->db],
        ]);

        if (!is_callable([$app, 'getOutbox'])) {
            throw new Exception('App without getOutbox method');
        }

        return $app->getOutbox();
    }
}
