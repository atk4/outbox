<?php

namespace atk4\outbox\Model;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\outbox\Outbox;

/**
 * Class Mail
 */
class Mail extends Model
{
    public const STATUS_DRAFT   = 'DRAFT';
    public const STATUS_READY   = 'READY';
    public const STATUS_SENDING = 'SENDING';
    public const STATUS_SENT    = 'SENT';
    public const STATUS_ERROR   = 'ERROR';

    public const MAIL_STATUS = [
        0 => self::STATUS_DRAFT,
        1 => self::STATUS_READY,
        2 => self::STATUS_SENDING,
        3 => self::STATUS_SENT,
        5 => self::STATUS_ERROR,
    ];

    public $table = 'mail';

    /**
     * @throws \atk4\core\Exception
     * @throws Exception
     */
    public function init()
    {
        parent::init();

        $this->containsOne('from', MailAddress::class);

        $this->containsMany('replyto', MailAddress::class);

        $this->containsMany('to', MailAddress::class);
        $this->containsMany('cc', MailAddress::class);
        $this->containsMany('bcc', MailAddress::class);

        $this->addField('subject');

        $this->addField('text', ['type' => 'text']);
        $this->addField('html', ['type' => 'text']);

        $this->containsMany('attachments', MailAttachment::class);

        $this->addField('sent_at', ['type' => 'datetime']);

        $this->addField('postpone_to', ['type' => 'datetime']);

        $this->addField(
            'status', [
            'type'    => 'enum',
            'values'  => static::MAIL_STATUS,
            'default' => 0,
        ]);

        $this->hasMany('response', MailResponse::class);
    }

    /**
     * Set data from MailTemplate
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

        foreach ($template->get() as $key => $value) {
            if ($this->mail->offsetExists($key)) {
                $this->mail->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @throws Exception
     *
     * @return Mail
     */
    public function withTemplateIdentifier(string $identifier): Mail
    {
        $template = new MailTemplate($this->persistence);
        $template->load($identifier);

        $this->withTemplate($template);

        return $this;
    }

    /**
     * Check if can be processed
     * @throws Exception
     */
    private function allowProcessing(): void
    {
        if ((int)$this->get('status') !== 0) {
            throw new Exception('You cannot modify a mail not in draft status');
        }
    }

    /**
     * @param string|array<string,string>|Model            $tokens
     * @param string|null $prefix
     *
     * @throws Exception
     * @return Mail
     */
    public function replaceContent($tokens, ?string $prefix = null): Mail
    {
        if(is_string($tokens)) {
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
     * Replace in subject, html and text using key with value
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

    public function send(?Outbox $outbox = null)
    {
        if ($outbox === null && null !== $this->app && method_exists($this->app, 'getOutbox')) {
            $outbox = $this->app->getOutbox();
        }

        $outbox->send($this);
    }
}