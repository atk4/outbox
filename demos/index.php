<?php

declare(strict_types=1);

namespace Atk4\Outbox\Demos;

use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Outbox;
use Atk4\Outbox\Tests\FakeMailer;

include dirname(__DIR__) . '/vendor/autoload.php';

/** @var App $app */
require __DIR__ . '/init-app.php';

$app->add([Outbox::class, [
    'mailer' => new FakeMailer(),
    'model' => new Mail($app->db),
]]);
