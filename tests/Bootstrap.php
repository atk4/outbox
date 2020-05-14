<?php

namespace atk4\outbox\Test;

use atk4\core\CollectionTrait;
use atk4\data\Exception;
use atk4\data\Persistence;
use atk4\outbox\Model\Mail;
use atk4\outbox\Model\MailResponse;
use atk4\outbox\Model\MailTemplate;
use atk4\schema\Migration;

class Bootstrap
{
    use CollectionTrait;

    /** @var self */
    private static $instance;

    public $elements = [];

    public function setup()
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

        Migration::of($mail)->run();
        Migration::of($mail_template)->run();
        Migration::of($mail_response)->run();
        Migration::of($user)->run();

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

        self::$instance = new static();

        return self::$instance;
    }

    public function el($name, $obj = null)
    {
        if ($obj === null) {
            return $this->_getFromCollection($name, 'elements');
        }

        return $this->_addIntoCollection($name, $obj, 'elements');
    }

    /**
     * @throws Exception
     * @throws \atk4\core\Exception
     */
    private function prepareMailTemplate(MailTemplate $mail_template): void
    {
        $mail_template->tryLoadBy('identifier', 'template_test');
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

    /**
     * @throws Exception
     * @throws \atk4\core\Exception
     */
    private function prepareMailTemplateUser(MailTemplate $mail_template): void
    {
        $mail_template->tryLoadBy('identifier', 'template_test_user');
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
