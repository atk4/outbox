<?php

declare(strict_types=1);

namespace Atk4\Outbox;

use Atk4\Core\Exception;
use Atk4\Core\Factory;
use Atk4\Data\Persistence;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use Atk4\Ui\AbstractView;

class Outbox extends AbstractView
{
    /**
     * Mailer.
     *
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * Default Mail model.
     *
     * @var Mail|array|string
     */
    protected $model = Mail::class;

    /** @var string */
    protected $skin;

    public function __construct(array $defaults = [])
    {
        if (is_array($defaults['mailer'])) {
            $defaults['mailer'] = Factory::factory($defaults['mailer']);
        }

        if (!is_a($defaults['mailer'], MailerInterface::class, true)) {
            throw new Exception('Mailer is not a MailerInterface');
        }

        $this->setDefaults($defaults);

        if ($this->mailer === null) {
            throw new Exception('No Mailer');
        }
    }

    protected function init(): void
    {
        parent::init();

        // Setup app, if present
        $this->getApp()->addMethod(
            'getOutbox',
            function (): self {
                return $this;
            }
        );

        if (is_array($this->model)) {
            if (!is_a($this->model[1] ?? null, Persistence::class)) {
                $this->model[1] = $this->getApp()->db;
            }
        }

        if (is_string($this->model)) {
            $this->model = [
                $this->model,
                $this->getApp()->db,
            ];
        }

        $this->mailer = Factory::factory($this->mailer);
        $this->model = Factory::factory($this->model);

        if (!is_a($this->mailer, MailerInterface::class)) {
            throw (new Exception('Mailer must be a subclass of MailerInterface'))
                ->addSolution('You need to specify a Mailer which implements MailerInterface');
        }

        if (!is_a($this->model, Mail::class)) {
            throw new Exception('mail_model must be a subclass of Model Mail');
        }

        if ($this->model->loaded()) {
            throw new Exception('mail_model cannot be an already loaded model (used as a base for all mail)');
        }
    }

    public function callableSend(callable $send): MailResponse
    {
        $mail = $send($this->new());

        return $this->send($mail);
    }

    public function new(): Mail
    {
        $this->validateOutbox();

        return clone $this->model;
    }

    protected function validateOutbox(): void
    {
        if (!$this->_initialized) {
            throw (new Exception('Outbox must be initialized first'))
                ->addSolution('if you use outbox with App, outbox must be add to app using method App::add')
                ->addSolution('if you use outbox without App, you need to call init() before use');
        }
    }

    public function send(Mail $mail): MailResponse
    {
        $this->validateOutbox();

        $mail->hook('beforeSend');
        $response = $this->mailer->send($mail);
        $mail->hook('afterSend', [$response]);

        return $response;
    }
}
