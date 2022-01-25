<?php

declare(strict_types=1);

namespace Atk4\Outbox\Tests;

use Atk4\Data\Schema\Migrator;
use Atk4\Data\Schema\TestCase as BaseTestCase;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use Atk4\Outbox\Model\MailTemplate;

abstract class GenericTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupDefaultDb();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function setupDefaultDb(): void
    {
        (new Migrator(new Mail($this->db)))->dropIfExists()->create();
        (new Migrator(new MailResponse($this->db)))->dropIfExists()->create();
        (new Migrator(new MailTemplate($this->db)))->dropIfExists()->create();
        (new Migrator(new User($this->db)))->dropIfExists()->create();

        $this->prepareMailTemplate();
        $this->prepareMailTemplateUser();

        (new User($this->db))->createEntity()->save([
            'email' => 'user@email.it',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    private function prepareMailTemplate(): void
    {
        $mail_template = (new MailTemplate($this->db))->tryLoadBy('identifier', 'template_test');

        if ($mail_template->isLoaded()) {
            return;
        }

        $mail_template->set('identifier', 'template_test');

        $mail_template->set('from', [
            'email' => 'sender@email.it',
            'name' => 'sender',
        ]);

        $mail_template->set('subject', 'subject mail for {{token}}');

        $content = 'hi to all,|this is outbox library of {{token}}.||have a good day.';

        $mail_template->set('html', str_replace('|', '<br/>', $content));
        $mail_template->set('text', str_replace('|', PHP_EOL, $content));
        $mail_template->save();
    }

    private function prepareMailTemplateUser(): void
    {
        $mail_template = (new MailTemplate($this->db))->tryLoadBy('identifier', 'template_test_user');

        if ($mail_template->isLoaded()) {
            return;
        }

        $mail_template->set('identifier', 'template_test_user');

        $mail_template->set('from', [
            'email' => 'sender@email.it',
            'name' => 'sender',
        ]);

        $content = 'hi to all,|this is outbox library of {{token}}.||have a good day.||{{user.first_name}} {{user.last_name}}';

        $mail_template->set('subject', 'subject mail for {{user.name}}');
        $mail_template->set('html', str_replace('|', '<br/>', $content));
        $mail_template->set('text', str_replace('|', PHP_EOL, $content));
        $mail_template->save();
    }
}
