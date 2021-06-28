<?php

declare(strict_types=1);

$db_file = __DIR__ . '/db.sqlite';
$db_exists = file_exists($db_file);

use Atk4\Data\Persistence;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use Atk4\Outbox\Model\MailTemplate;
use Atk4\Outbox\Test\User;

$db = Persistence::connect('sqlite:' . $db_file);

if (!$db_exists) {
    (new \Atk4\Schema\Migration(new Mail($db)))->dropIfExists()->create();
    (new \Atk4\Schema\Migration(new MailTemplate($db)))->dropIfExists()->create();
    (new \Atk4\Schema\Migration(new MailResponse($db)))->dropIfExists()->create();
    (new \Atk4\Schema\Migration(new User($db)))->dropIfExists()->create();
}

return $db;
