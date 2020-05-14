<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Model;

class MailTemplate extends Model
{
    public $table = 'mail_template';

    public function init(): void
    {
        parent::init();

        $this->addField('identifier');

        $this->containsOne('from', MailAddress::class);
        $this->containsOne('replyto', MailAddress::class);

        $this->containsOne('header', MailHeader::class);

        $this->containsMany('to', MailAddress::class);
        $this->containsMany('cc', MailAddress::class);
        $this->containsMany('bcc', MailAddress::class);

        $this->addField('subject');

        $this->addField('text', ['type' => 'text']);
        $this->addField('html', ['type' => 'text']);

        $this->containsMany('attachment', MailAttachment::class);

        $this->containsMany('tokens', MailTemplateToken::class);

        $this->onHook('beforeSave', function (self $m) {
            $m->refreshTokens();
        }, [], -200);
    }

    public function refreshTokens()
    {
        $re = '/.*{{(.*)}}/m';

        $matches = [];

        $tmp = [];
        preg_match_all($re, $this->get('subject'), $tmp, PREG_SET_ORDER, 0);
        $matches = array_merge($matches, $tmp);

        $tmp = [];
        preg_match_all($re, $this->get('html'), $tmp, PREG_SET_ORDER, 0);
        $matches = array_merge($matches, $tmp);

        $tmp = [];
        preg_match_all($re, $this->get('text'), $tmp, PREG_SET_ORDER, 0);
        $matches = array_merge($matches, $tmp);

        $tokens = $this->ref('tokens')->export(null, 'token');
        $new_tokens = [];

        $this->set('tokens', []);

        // @todo can be done better?
        foreach ($matches as [$match, $token]) {
            if (in_array($token, $tokens, true)) {
                continue;
            }

            $new_tokens[$token] = [
                'token' => $token,
                'description' => $tokens[$token]['description'] ?? '',
            ];
        }

        $this->set('tokens', array_values($new_tokens));
    }
}
