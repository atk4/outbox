<?php

namespace Atk4\Outbox\Model;

use Atk4\Data\Model;
use Atk4\Data\Persistence\Array_;

abstract class AbstractMailModel extends Model
{
    protected function init(): void
    {
        parent::init();

        $this->containsOne('from', ['model' => [MailAddress::class]]);
        $this->containsMany('replyto', ['model' => [MailAddress::class]]);

        $this->containsMany('headers', ['model' => [MailHeader::class]]);

        $this->containsMany('to', ['model' => [MailAddress::class]]);
        $this->containsMany('cc', ['model' => [MailAddress::class]]);
        $this->containsMany('bcc', ['model' => [MailAddress::class]]);

        $this->addField('subject');

        $this->addField('text', ['type' => 'text']);
        $this->addField('html', ['type' => 'text']);

        $this->containsMany('attachments', ['model' => [MailAttachment::class]]);
    }
}