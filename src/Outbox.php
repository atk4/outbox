<?php

declare(strict_types=1);

namespace Atk4\Outbox;

use Atk4\Core\Exception;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use Atk4\Ui\AbstractView;

class Outbox extends AbstractView
{
    /**
     * Mailer.
     */
    protected ?MailerInterface $mailer;

    /**
     * Default Mail model.
     */
    protected ?Mail $model;

    public function __construct(array $defaults = [])
    {
        // using typed property, let it crash in set defaults
        $this->setDefaults($defaults);
    }

    public function callableSend(callable $send): MailResponse
    {
        $mail = $send($this->new());

        return $this->send($mail);
    }

    public function new(): Mail
    {
        $this->validateOutbox();

        return $this->model->createEntity();
    }

    protected function validateOutbox(): void
    {
        if (!$this->isInitialized()) {
            throw (new Exception('Outbox must be initialized first'))
                ->addSolution('if you use outbox with App, outbox must be add to app using method Outbox::addTo')
                ->addSolution('if you use outbox without App, you need to call invokeInit() before use');
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

    protected function init(): void
    {
        parent::init();

        // required
        if (empty($this->mailer)) {
            throw new Exception('mailer is required');
        }

        if (empty($this->model)) {
            throw new Exception('mail model is required');
        }

        $this->getApp()->addMethod('getOutbox', fn (): self => $this);
    }
}
