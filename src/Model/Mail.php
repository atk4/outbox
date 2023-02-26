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
    /**
     * @var string
     */
    public const HOOK_BEFORE_SEND = Model::class . '@beforeSend';

    /**
     * @var string
     */
    public const HOOK_AFTER_SEND = Model::class . '@afterSend';

    /**
     * @var string
     */
    public const STATUS_DRAFT = 'DRAFT';

    /**
     * @var string
     */
    public const STATUS_READY = 'READY';

    /**
     * @var string
     */
    public const STATUS_SENDING = 'SENDING';

    /**
     * @var string
     */
    public const STATUS_SENT = 'SENT';

    /**
     * @var string
     */
    public const STATUS_ERROR = 'ERROR';

    /**
     * @var string[]
     */
    public const MAIL_STATUS = [
        self::STATUS_DRAFT,
        self::STATUS_READY,
        self::STATUS_SENDING,
        self::STATUS_SENT,
        self::STATUS_ERROR,
    ];

    public $table = 'mail';

    public $caption = 'Mail';

    public string $mail_template_default = MailTemplate::class;

    public function withTemplateIdentifier(string $identifier): self
    {
        /** @var MailTemplate $template */
        $template = new $this->mail_template_default($this->getPersistence());
        $mailTemplate = $template->tryLoadBy('identifier', $identifier);

        if (!$mailTemplate->isLoaded()) {
            throw new Exception('template "' . $identifier . '" not exists');
        }

        $this->withTemplate($mailTemplate);

        return $this;
    }

    /**
     * Set data from MailTemplate.
     */
    public function withTemplate(MailTemplate $mailTemplate): self
    {
        $this->allowProcessing();

        foreach ($mailTemplate->getFields() as $fieldname => $field) {
            if ($fieldname === $this->idField) {
                continue;
            }

            if (!$this->hasField($fieldname)) {
                continue;
            }

            if ($field->hasReference()) {
                if ($field->getReference() instanceof \Atk4\Data\Reference\ContainsMany) {
                    $this->ref($fieldname)->import($mailTemplate->get($fieldname) ?? []);
                }

                if ($field->getReference() instanceof \Atk4\Data\Reference\ContainsOne) {
                    $this->ref($fieldname)->save($mailTemplate->get($fieldname) ?? []);
                }

                continue;
            }

            $this->set($fieldname, $mailTemplate->get($fieldname));
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

    public function saveAsTemplate(string $identifier, bool $overwrite = false): MailTemplate
    {
        $mailTemplate = new MailTemplate($this->getPersistence());
        $mailTemplate->addCondition('identifier', $identifier);

        $entity_template = $mailTemplate->tryLoadAny();

        if ($overwrite && $entity_template->isLoaded()) {
            throw new \Atk4\Ui\Exception('Template Identifier already exists');
        }

        if (!$overwrite) {
            $entity_template = $mailTemplate->createEntity();
        }

        foreach ($this->getFields() as $fieldname => $field) {
            if ($fieldname === $this->idField) {
                continue;
            }

            if (!$entity_template->hasField($fieldname)) {
                continue;
            }

            if ($field->hasReference()) {
                if ($field->getReference() instanceof \Atk4\Data\Reference\ContainsMany && !empty($this->get($fieldname))) {
                    $entity_template->ref($fieldname)->import($this->get($fieldname));
                }

                if ($field->getReference() instanceof \Atk4\Data\Reference\ContainsOne && !empty($this->get($fieldname))) {
                    $entity_template->ref($fieldname)->save($this->get($fieldname));
                }

                continue;
            }

            $entity_template->set($fieldname, $this->get($fieldname));
        }

        return $entity_template->save();
    }

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
            'theirField' => 'email_id',
        ]);
    }
}
