<?php

declare(strict_types=1);

namespace Atk4\Outbox\Model;

use Atk4\Data\Model;
use DateTime;

class MailResponse extends Model
{
    public $table = 'mail_response';

    protected function init(): void
    {
        parent::init();

        $this->hasOne('email_id', ['model' => [Mail::class]]);

        $this->addField('code', ['type' => 'integer', 'default' => 0]);
        $this->addField(
            'message',
            ['type' => 'string', 'default' => 'success']
        );

        $this->addField(
            'timestamp',
            ['type' => 'datetime', 'default' => new DateTime()]
        );
    }
}
