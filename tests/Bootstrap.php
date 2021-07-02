<?php

declare(strict_types=1);

namespace Atk4\Outbox\Test;

use Atk4\Core\CollectionTrait;
use Atk4\Data\Persistence;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use Atk4\Outbox\Model\MailTemplate;
use Atk4\Schema\Migration;

class Bootstrap
{
    use CollectionTrait;

    /** @var self */
    private static $instance;

    /** @var array */
    public $elements = [];

    public function setup(): void
    {
        if (!empty(self::instance()->elements)) {
            return;
        }

        $self = self::instance();

        $persistence = Persistence::connect(getenv('MYSQL_DSN'));
        $mail = new Mail($persistence);
        $mail_template = new MailTemplate($persistence);
        $mail_response = new MailResponse($persistence);
        $user = new User($persistence);

        (new Migration($mail))->dropIfExists()->create();
        (new Migration($mail_template))->dropIfExists()->create();
        (new Migration($mail_response))->dropIfExists()->create();
        (new Migration($user))->dropIfExists()->create();

        $self->el('persistence', $persistence);
        $self->el('mail_model', $mail);
        $self->el('mail_template', $mail_template);
        $self->el('mail_response', $mail_response);
        $self->el('user_model', $user);

        $this->prepareMailTemplate($mail_template);
        $this->prepareMailTemplateUser($mail_template);

        $user->addCondition('email', 'user@email.it');
        $user->tryLoadAny();

        $user->save([
            'email' => 'user@email.it',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public static function instance(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }

    public function el(string $name, object $obj = null): object
    {
        if ($obj === null) {
            return $this->_getFromCollection($name, 'elements');
        }

        return $this->_addIntoCollection($name, $obj, 'elements');
    }

    private function prepareMailTemplate(MailTemplate $mail_template): void
    {
        $mail_template = $mail_template->newInstance()->tryLoadBy('identifier', 'template_test');

        if ($mail_template->loaded()) {
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

    private function prepareMailTemplateUser(MailTemplate $mail_template): void
    {
        $mail_template = $mail_template->newInstance()->tryLoadBy('identifier', 'template_test_user');

        if ($mail_template->loaded()) {
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
