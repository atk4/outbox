<?php

declare(strict_types=1);

namespace Atk4\Outbox\Tests;

use Atk4\Data\Model;
use Atk4\Data\Persistence\Array_;
use Atk4\Outbox\Model\MailAddress;

class User extends Model
{
    public $table = 'user';

    public ?string $titleField = 'email';

    protected function init(): void
    {
        parent::init();

        $this->addField('first_name');
        $this->addField('last_name');

        $this->addField('email');

        // $this->addExpression('name', '([first_name] || [last_name])');
    }

    public function getMailAddress(): MailAddress
    {
        $mailAddress = (new MailAddress(new Array_()))->createEntity();
        $mailAddress->set('email', $this->get('email'));
        $mailAddress->set('name', $this->get('first_name') . ' ' . $this->get('last_name'));

        return $mailAddress;
    }
}
