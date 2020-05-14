<?php

use atk4\data\Persistence;
use atk4\outbox\Model\Mail;
use atk4\outbox\Model\MailResponse;
use atk4\outbox\Model\MailTemplate;
use atk4\outbox\Test\User;
use atk4\schema\Migration;

$db = Persistence::connect('mysql://atk4_test:atk4_pass@localhost/atk4_test__outbox');

Migration::of(new Mail($db))->run();
Migration::of(new MailTemplate($db))->run();
Migration::of(new MailResponse($db))->run();
Migration::of(new User($db))->run();