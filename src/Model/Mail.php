<?php

declare(strict_types=1);

namespace atk4\outbox\Model;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\outbox\Outbox;
use Html2Text\Html2Text;

/**
 * Class Mail.
 */
class Mail extends Model
{
    public const STATUS_DRAFT   = 'DRAFT';
    public const STATUS_READY   = 'READY';
    public const STATUS_SENDING = 'SENDING';
    public const STATUS_SENT    = 'SENT';
    public const STATUS_ERROR   = 'ERROR';

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

    /**
     * @throws \atk4\core\Exception
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        $this->containsOne('from', MailAddress::class);
        $this->containsMany('replyto', MailAddress::class);

        $this->containsMany('headers', MailHeader::class);

        $this->containsMany('to', MailAddress::class);
        $this->containsMany('cc', MailAddress::class);
        $this->containsMany('bcc', MailAddress::class);

        $this->addField('subject');

        $this->addField('text', ['type' => 'text']);
        $this->addField('html', ['type' => 'text']);

        $this->containsMany('attachments', MailAttachment::class);

        $this->addField('status', [
            'values'  => array_combine(
                static::MAIL_STATUS,
                static::MAIL_STATUS
            ),
            'default' => static::STATUS_DRAFT,
        ]);

        $this->hasMany('response', [
            MailResponse::class,
            'their_field' => "email_id",
        ]);
    }

    /**
     * @param string $identifier
     *
     * @throws Exception|\atk4\core\Exception
     *
     * @return Mail
     */
    public function withTemplateIdentifier(string $identifier): Mail
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
     *
     * @param MailTemplate $template
     *
     * @throws Exception
     *
     * @return Mail
     */
    public function withTemplate(MailTemplate $template): Mail
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
     *
     * @throws Exception
     */
    private function allowProcessing(): void
    {
        if (0 !== (int)$this->get('status')) {
            throw new Exception('You cannot modify a mail not in draft status');
        }
    }

    /**
     * @param string|array<string,string>|Model $tokens
     * @param string|null                       $prefix
     *
     * @throws Exception
     * @return Mail
     */
    public function replaceContent($tokens, ?string $prefix = null): Mail
    {
        if (is_string($tokens)) {
            $tokens = [$tokens => $prefix];
            $prefix = null;
        }

        if (is_a($tokens, Model::class, true)) {
            $tokens = $tokens->get();
        }

        foreach ($tokens as $key => $value) {
            $key = '{{' . (null === $prefix ? $key : $prefix . '.' . $key) . '}}';
            $this->replaceContentToken($key, $value);
        }

        return $this;
    }

    /**
     * Replace in subject, html and text using key with value.
     *
     * @param string $key
     * @param string $value
     *
     * @throws Exception
     *
     * @return Mail
     */
    private function replaceContentToken(string $key, string $value): Mail
    {
        $this->allowProcessing();

        $this->set('subject', str_replace($key, $value, $this->get('subject')));
        $this->set('html', str_replace($key, $value, $this->get('html')));
        $this->set('text', str_replace($key, $value, $this->get('text')));

        return $this;
    }

    /**
     * Send Mail using $outbox or get from app
     *
     * @param Outbox|null $outbox
     *
     * @throws \atk4\core\Exception
     *
     * @return MailResponse
     */
    public function send(?Outbox $outbox = null): MailResponse
    {
        // if outbox is null check if App is present and has outbox added
        if (null === $outbox && null !== $this->app && method_exists(
            $this->app,
            'getOutbox'
        )) {
            $outbox = $this->app->getOutbox();
        }

        // if still null throw exception
        if (null === $outbox) {
            throw new Exception([
                '$outbox is null and App has no Outbox',
                'solutions' => [
                    'Add Outbox object to App',
                    'Call method send with Outbox != null',
                ],
            ]);
        }

        return $outbox->send($this);
    }

    public function saveAsTemplate(string $identifier): MailTemplate
    {
        $mail_template = new MailTemplate($this->persistence);
        $mail_template->addCondition('identifier', $identifier);
        $mail_template->tryLoadAny();

        if ($mail_template->loaded()) {
            throw new \atk4\ui\Exception([
                'Template Identifier already exists',
            ]);
        }

        foreach ($this->get() as $fieldname => $value) {
            if ($fieldname !== $this->id_field && $mail_template->hasField($fieldname)) {
                $mail_template->set($fieldname, $value);
            }
        }

        return $mail_template->save();
    }
}
