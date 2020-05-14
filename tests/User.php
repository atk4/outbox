<?php

namespace atk4\outbox\Test;

use atk4\data\Model;
use atk4\data\Persistence\Static_;
use atk4\outbox\Model\MailAddress;

class User extends Model
{
    public $table = 'user';

    public function init(): void
    {
        parent::init();

        $this->addField('first_name');
        $this->addField('last_name');

        $this->addField('email');

        $this->addExpression('name', 'CONCAT([first_name], " ", [last_name])');
    }

    public function getMailAddress(): MailAddress
    {
        $p = new Static_([]);
        $address = new MailAddress($p);
        $address->set('email', $this->get('email'));
        $address->set('name', $this->get('name'));

        return $address;
    }
}
