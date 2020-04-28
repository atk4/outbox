<?php

declare(strict_types=1);

namespace atk4\outbox;

use atk4\core\AppScopeTrait;
use atk4\core\DIContainerTrait;
use atk4\core\Exception;
use atk4\core\FactoryTrait;
use atk4\core\InitializerTrait;
use atk4\data\Persistence;
use atk4\outbox\Model\Mail;

class Outbox
{
    use AppScopeTrait;
    use InitializerTrait {
        init as _init;
    }
    use DIContainerTrait;
    use FactoryTrait;

    /**
     * Mailer.
     *
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * Default Mail model.
     *
     * @var Mail
     */
    protected $model = Mail::class;

    public function __construct($defaults = [])
    {
        if (empty($defaults['mailer'])) {
            throw new Exception('No Mailer');
        }

        if (is_array($defaults['mailer'])) {
            $class = array_pop($defaults['mailer']);
            $defaults['mailer'] = new $class($defaults['mailer']);
        }

        $this->setDefaults($defaults);
    }

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $this->_init();

        // Setup app, if present
        if (null !== $this->app) {
            $this->app->addMethod(
                'getOutbox',
                function (): self {
                    return $this;
                }
            );

            if (is_array($this->model)) {
                if (!is_a($this->model[1] ?? null, Persistence::class)) {
                    $this->model[1] = $this->app->db;
                }
            }

            if (is_string($this->model)) {
                $this->model = [
                    $this->model,
                    $this->app->db,
                ];
            }
        }

        $this->mailer = $this->factory($this->mailer);
        $this->model = $this->factory($this->model);

        if (!is_a($this->mailer, MailerInterface::class)) {
            $exc = new Exception('Mailer must be a subclass of MailerInterface');
            throw $exc->addSolution('You need to specify a Mailer which implements MailerInterface');
        }

        if (!is_a($this->model, Mail::class)) {
            throw new Exception('mail_model must be a subclass of Model Mail');
        }

        if ($this->model->loaded()) {
            throw new Exception('mail_model cannot be an already loaded model (used as a base for all mail)');
        }
    }

    public function callableSend(callable $send): void
    {
        $mail = $send($this->new());
        $this->send($mail);
    }

    public function new(): Mail
    {
        return clone $this->model;
    }

    /**
     * @param Mail $mail
     *
     * @return Mail
     * @throws Exception
     *
     */
    public function send(Mail $mail): Mail
    {
        $this->validateOutbox();

        $mail->hook('beforeSend');
        $this->mailer->send($mail);
        $mail->hook('afterSend');
    }

    /**
     * @throws Exception
     */
    protected function validateOutbox(): void
    {
        if (!$this->_initialized) {
            $exc = new Exception('Outbox must be initialized first');
            $exc->addSolution('if you use outbox with App, outbox must be add to app using method App::add');
            throw $exc->addSolution('if you use outbox without App, you need to call init() before use');
        }
    }
}
