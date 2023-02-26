<?php

declare(strict_types=1);

namespace Atk4\Outbox\Model;

use Atk4\Data\Model;

class MailTemplate extends Model
{
    public $table = 'mail_template';

    public $caption = 'Mail Template';

    public ?string $titleField = 'identifier';

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

        $this->onHook(Model::HOOK_AFTER_SAVE, static function (self $m) {
            $m->refreshTokens();
        }, [], -200);
    }

    public function refreshTokens(): void
    {
        $original_tokens = $this->get('tokens') ?? [];
        $tokens = [];
        foreach ($original_tokens as $token) {
            $tokens[$token['token']] = [
                'token' => $token['token'],
                'description' => $token['description'],
            ];
        }

        $this->setNull('tokens');

        $re = '/.*{{(.*)}}/m';

        $matches = [];

        $tmp = [];
        preg_match_all($re, $this->get('subject') ?? '', $tmp, \PREG_SET_ORDER, 0);
        $matches = array_merge($matches, $tmp);

        $tmp = [];
        preg_match_all($re, $this->get('html') ?? '', $tmp, \PREG_SET_ORDER, 0);
        $matches = [...$matches, ...$tmp];

        $tmp = [];
        preg_match_all($re, $this->get('text') ?? '', $tmp, \PREG_SET_ORDER, 0);
        $matches = [...$matches, ...$tmp];

        $new_tokens = [];
        foreach ($matches as $match) {
            $token = $match[1];
            $new_tokens[$token] = [
                'token' => $token,
                'description' => $tokens[$token]['description'] ?? '',
            ];
        }

        $this->ref('tokens')->import(array_values($new_tokens));
    }
}
