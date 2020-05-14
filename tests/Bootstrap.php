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
        $self->_addIntoCollection(
            'persistence',
            Persistence::connect(getenv('MYSQL_DSN')),
            'elements'
        );
        $self->_addIntoCollection(
            'mail_model',
            new Mail($self->_getFromCollection('persistence', 'elements')),
            'elements'
        );
        $self->_addIntoCollection(
            'mail_template',
            new MailTemplate($self->_getFromCollection(
                'persistence',
                'elements'
            )),
            'elements'
        );
        $self->_addIntoCollection(
            'mail_response',
            new MailResponse($self->_getFromCollection(
                'persistence',
                'elements'
            )),
            'elements'
        );
        $self->_addIntoCollection(
            'user_model',
            new User($self->_getFromCollection('persistence', 'elements')),
            'elements'
        );

        Migration::of($self->_getFromCollection(
            'mail_model',
            'elements'
        ))->run();
        Migration::of($self->_getFromCollection(
            'mail_template',
            'elements'
        ))->run();
        Migration::of($self->_getFromCollection(
            'mail_response',
            'elements'
        ))->run();
        Migration::of($self->_getFromCollection(
            'user_model',
            'elements'
        ))->run();

        /** @var MailTemplate $mail_template */
        $mail_template = $self->_getFromCollection('mail_template', 'elements');
        $this->prepareMailTemplate($mail_template);
        $this->prepareMailTemplateUser($mail_template);

        /** @var User $user */
        $user = $self->_getFromCollection('user_model', 'elements');
        $user->addCondition('email', 'user@email.it');
        $user->tryLoadAny();

        $user->save([
            'email'      => 'user@email.it',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);
    }

    public static function instance(): self
    {
        if (null !== self::$instance) {
            return self::$instance;
        }

        self::$instance = new static();

        return self::$instance;
    }

    /**
     * @param MailTemplate $mail_template
     *
     * @throws Exception
     */
    private function prepareMailTemplate(MailTemplate $mail_template): void
    {
        $mail_template = $mail_template->newInstance();
        $mail_template->addCondition('identifier', 'template_test');
        $mail_template->tryLoadAny();

        $mail_template->set('from', [
            "email" => 'sender@email.it',
            "name"  => "sender",
        ]);

        $mail_template->set('subject', 'subject mail for {{token}}');

        $content = 'hi to all,|this is outbox library of {{token}}.||have a good day.';

        $mail_template->set('html', str_replace('|', '<br/>', $content));
        $mail_template->set('text', str_replace('|', PHP_EOL, $content));
        $mail_template->save();
    }

    /**
     * @param MailTemplate $mail_template
     *
     * @throws Exception
     */
    private function prepareMailTemplateUser(MailTemplate $mail_template): void
    {
        $mail_template = $mail_template->newInstance();
        $mail_template->addCondition('identifier', 'template_test_user');
        $mail_template->tryLoadAny();

        $mail_template->set('from', [
            "email" => 'sender@email.it',
            "name"  => "sender",
        ]);

        $content = 'hi to all,|this is outbox library of {{token}}.||have a good day.||{{user.first_name}} {{user.last_name}}';

        $mail_template->set('subject', 'subject mail for {{user.name}}');
        $mail_template->set('html', str_replace('|', '<br/>', $content));
        $mail_template->set('text', str_replace('|', PHP_EOL, $content));
        $mail_template->save();
    }
}
