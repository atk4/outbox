<?php

declare(strict_types=1);

$db_file = __DIR__ . '/db.sqlite';
$db_exists = file_exists($db_file);

use Atk4\Data\Schema\Migrator;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use Atk4\Outbox\Model\MailTemplate;
use Atk4\Outbox\Test\User;

$db = new \Atk4\Data\Persistence\Sql('sqlite:' . $db_file);

if (!$db_exists) {
    (new Migrator(new Mail($db)))->dropIfExists()->create();
    (new Migrator(new MailTemplate($db)))->dropIfExists()->create();
    (new Migrator(new MailResponse($db)))->dropIfExists()->create();
    (new Migrator(new User($db)))->dropIfExists()->create();
}

return $db;
