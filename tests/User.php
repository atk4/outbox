<?php

declare(strict_types=1);

namespace Atk4\Outbox\Test;

use Atk4\Data\Model;
use Atk4\Data\Persistence\Array_;
use Atk4\Outbox\Model\MailAddress;

class User extends Model
{
    public $table = 'user';

    public $title_field = 'email';

    protected function init(): void
    {
        parent::init();

        $this->addField('first_name');
        $this->addField('last_name');

        $this->addField('email');

        //$this->addExpression('name', '([first_name] || [last_name])');
    }

    public function getMailAddress(): MailAddress
    {
        $address = new MailAddress(new Array_());
        $address->set('email', $this->get('email'));
        $address->set('name', $this->get('first_name') . ' ' . $this->get('last_name'));

        return $address;
    }
}
