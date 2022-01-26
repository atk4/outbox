<?php

declare(strict_types=1);

namespace Atk4\Outbox\Tests;

use Atk4\Data\Schema\TestCase as BaseTestCase;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use Atk4\Outbox\Model\MailTemplate;

abstract class GenericTestCase extends BaseTestCase
{
    protected function setupDefaultDb(): void
    {
        $template_body = 'hi to all,|this is outbox library of {{token}}.||have a good day.';
        $template_user_body = 'hi to all,|this is outbox library of {{token}}.||have a good day.||{{user.first_name}} {{user.last_name}}';

        $this->setDb([
            $this->getReflectionModelTableName(Mail::class) => [
                [
                    'id' => 1,
                    'identifier' => 'template_test',
                    'from' => '{"email":"sender@email.it","name":"sender"}',
                    'replyto' => '[]',
                    'headers' => '[]',
                    'to' => '[]',
                    'cc' => '[]',
                    'bcc' => '[]',
                    'subject' => 'subject',
                    'text' => 'text',
                    'html' => 'html',
                    'attachments' => '[]',
                    'status' => Mail::STATUS_READY,
                ],
            ],
            $this->getReflectionModelTableName(MailResponse::class) => [
                [
                    'id' => 1,
                    'email_id' => 1,
                    'code' => '',
                    'message' => '',
                    'timestamp' => (new \Datetime())->format('Y-m-d H:i:s'),
                ],
            ],
            $this->getReflectionModelTableName(MailTemplate::class) => [
                [
                    'id' => 1,
                    'identifier' => 'template_test',
                    'from' => '{"email":"sender@email.it","name":"sender"}',
                    'replyto' => '[]',
                    'headers' => '[]',
                    'to' => '[]',
                    'cc' => '[]',
                    'bcc' => '[]',
                    'subject' => 'subject mail for {{token}}',
                    'text' => str_replace('|', PHP_EOL, $template_body),
                    'html' => str_replace('|', '<br/>', $template_body),
                    'attachments' => '[]',
                    'tokens' => '[]',
                ],
                [
                    'id' => 2,
                    'identifier' => 'template_test_user',
                    'from' => '{"email":"sender@email.it","name":"sender"}',
                    'replyto' => '[]',
                    'headers' => '[]',
                    'to' => '[]',
                    'cc' => '[]',
                    'bcc' => '[]',
                    'subject' => 'subject mail for {{user.name}}',
                    'text' => str_replace('|', PHP_EOL, $template_user_body),
                    'html' => str_replace('|', '<br/>', $template_user_body),
                    'attachments' => '[]',
                    'tokens' => '[]',
                ],
            ],
            $this->getReflectionModelTableName(User::class) => [
                [
                    'id' => 1,
                    'email' => 'user@email.it',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
        ]);
    }

    private function getReflectionModelTableName(string $modelclass): string
    {
        return (new \ReflectionClass($modelclass))->getDefaultProperties()['table'];
    }
}
