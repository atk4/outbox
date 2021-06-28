<?php

declare(strict_types=1);

namespace Atk4\Outbox\Model;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Atk4\Outbox\Outbox;

/**
 * Class Mail.
 */
class Mail extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_READY = 'READY';
    public const STATUS_SENDING = 'SENDING';
    public const STATUS_SENT = 'SENT';
    public const STATUS_ERROR = 'ERROR';

    public const MAIL_STATUS = [
        self::STATUS_DRAFT,
        self::STATUS_READY,
        self::STATUS_SENDING,
        self::STATUS_SENT,
        self::STATUS_ERROR,
    ];

    public $table = 'mail';

    /** @var string */
    public $mail_template_default = MailTemplate::class;

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

        $this->addField('status', [
            'values' => array_combine(
                static::MAIL_STATUS,
                static::MAIL_STATUS
            ),
            'default' => static::STATUS_DRAFT,
        ]);

        $this->hasMany('response', [
            'model' => [MailResponse::class],
            'their_field' => 'email_id',
        ]);
    }

    public function withTemplateIdentifier(string $identifier): self
    {
        /** @var MailTemplate $template */
        $template = new $this->mail_template_default($this->persistence);
        $template->tryLoadBy('identifier', $identifier);

        if (!$template->loaded()) {
            throw new Exception('template "' . $identifier . '" not exists');
        }

        $this->withTemplate($template);

        return $this;
    }

    /**
     * Set data from MailTemplate.
     */
    public function withTemplate(MailTemplate $template): self
    {
        $this->allowProcessing();

        foreach ($template->get() as $fieldname => $value) {
            if ($fieldname !== $this->id_field && $this->hasField($fieldname)) {
                $this->set($fieldname, $value);
            }
        }

        return $this;
    }

    /**
     * Check if can be processed.
     */
    private function allowProcessing(): void
    {
        if ((int) $this->get('status') !== 0) {
            throw new Exception('You cannot modify a mail not in draft status');
        }
    }

    /**
     * @param string|array<string,string>|Model $tokens
     */
    public function replaceContent($tokens, string $prefix = null): self
    {
        if (is_string($tokens)) {
            $tokens = [$tokens => $prefix];
            $prefix = null;
        }

        if (is_a($tokens, Model::class, true)) {
            $tokens = $tokens->get();
        }

        foreach ($tokens as $key => $value) {
            if ($value === null) {
                continue;
            }
            $key = '{{' . ($prefix === null ? $key : $prefix . '.' . $key) . '}}';
            $this->replaceContentToken($key, (string) $value);
        }

        return $this;
    }

    /**
     * Replace in subject, html and text using key with value.
     */
    private function replaceContentToken(string $key, string $value): self
    {
        $this->allowProcessing();

        $this->set('subject', str_replace($key, $value, $this->get('subject')));
        $this->set('html', str_replace($key, $value, $this->get('html')));
        $this->set('text', str_replace($key, $value, $this->get('text')));

        return $this;
    }

    /**
     * Send Mail using $outbox or get from app.
     */
    public function send(Outbox $outbox): MailResponse
    {
        return $outbox->send($this);
    }

    public function saveAsTemplate(string $identifier): MailTemplate
    {
        $mail_template = new MailTemplate($this->persistence);
        $mail_template->addCondition('identifier', $identifier);
        $mail_template->tryLoadAny();

        if ($mail_template->loaded()) {
            throw new \Atk4\Ui\Exception('Template Identifier already exists');
        }

        foreach ($this->get() as $fieldname => $value) {
            if ($fieldname !== $this->id_field && $mail_template->hasField($fieldname)) {
                $mail_template->set($fieldname, $value);
            }
        }

        return $mail_template->save();
    }
}
