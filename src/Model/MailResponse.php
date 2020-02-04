<?php
declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailResponse extends Model
{
    public $table = 'mail_response';

    public function init(): void
    {
        parent::init();

        $this->hasOne("email_id", Mail::class);

        $this->addField("code", ['type' => 'int']);
        $this->addField("message", ['type' => 'string']);

        $this->addField("timestamp", ['type' => 'datetime', 'default' => new \DateTime()]);
    }
}
