<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailTemplate extends Model
{
    public $table = "mail_template";

    public function init(): void
    {
        parent::init();

        $this->addFile('identifier');

        $this->containsOne('from', MailAddress::class);
        $this->containsOne('replyto', MailAddress::class);

        $this->containsMany('to', MailAddress::class);
        $this->containsMany('cc', MailAddress::class);
        $this->containsMany('bcc', MailAddress::class);

        $this->addField('subject');

        $this->addField('text', ['type' => 'text']);
        $this->addField('html', ['type' => 'text']);

        $this->containsMany('attachment', MailAttachment::class);

        $this->containsMany('tokens', MailTemplateToken::class);
    }
}
