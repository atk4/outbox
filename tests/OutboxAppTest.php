<?php

declare(strict_types=1);

namespace Atk4\Outbox\Tests;

use Atk4\Core\Exception;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Outbox;
use Atk4\Ui\App;
use Atk4\Ui\Layout;

/**
 * Class OutboxTest.
 */
class OutboxAppTest extends BaseOutboxTestCase
{
    protected function getOutbox(): Outbox
    {
        $app = new App([
            'alwaysRun' => false,
            'callExit' => false,
            'catchExceptions' => false,
        ]);
        $app->initLayout([Layout::class]);

        Outbox::addTo($app, [
            'mailer' => new FakeMailer(),
            'model' => new Mail($this->db),
        ]);

        if (!is_callable([$app, 'getOutbox'])) {
            throw new Exception('App without getOutbox method');
        }

        return $app->getOutbox();
    }
}
