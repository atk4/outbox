<?php

declare(strict_types=1);

namespace Atk4\Outbox\Demos;

use Atk4\Data\Persistence;
use Atk4\Data\Schema\Migrator;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use Atk4\Outbox\Model\MailTemplate;
use Atk4\Outbox\Tests\User;

require_once __DIR__ . '/../init-autoloader.php';

$sqliteFile = __DIR__ . '/db.sqlite';
if (!file_exists($sqliteFile)) {
    new Persistence\Sql('sqlite:' . $sqliteFile);
}
unset($sqliteFile);

/** @var Persistence\Sql $db */
require_once __DIR__ . '/../init-db.php';

echo 'GITHUB_JOB : ' . getenv('GITHUB_JOB') . PHP_EOL . PHP_EOL;

if (getenv('GITHUB_JOB') === 'unit-test') {
    echo 'skip db creation in create-db' . PHP_EOL . PHP_EOL;

    return;
}

(new Migrator(new Mail($db)))->dropIfExists()->create();
(new Migrator(new MailTemplate($db)))->dropIfExists()->create();
(new Migrator(new MailResponse($db)))->dropIfExists()->create();
(new Migrator(new User($db)))->dropIfExists()->create();

echo 'import complete!' . PHP_EOL . PHP_EOL;
