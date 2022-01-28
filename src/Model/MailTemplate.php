<?php

declare(strict_types=1);

namespace Atk4\Outbox\Model;

use Atk4\Data\Model;

class MailTemplate extends Model
{
    public $table = 'mail_template';
    public $caption = 'Mail Template';

    protected function init(): void
    {
        parent::init();

        $this->addField('identifier');

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

        $this->containsMany('tokens', ['model' => [MailTemplateToken::class]]);

        $this->onHook('beforeSave', function (self $m) {
            $m->refreshTokens();
        }, [], -200);
    }

    public function refreshTokens(): void
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

        //$this->set('tokens', []);

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
