<?php

declare(strict_types=1);

namespace Atk4\Outbox\Model;

class MailTemplate extends AbstractMailModel
{
    public $table = 'mail_template';
    public $caption = 'Mail Template';

    protected function init(): void
    {
        parent::init();

        $this->addField('identifier');

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

        // $this->set('tokens', []);

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
