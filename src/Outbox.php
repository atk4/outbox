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
use atk4\outbox\Model\MailResponse;

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
        if (is_array($defaults['mailer'])) {
            $defaults['mailer'] = $this->factory(
                array_shift($defaults['mailer']),
                $defaults['mailer']
            );
        }

        if (!is_a($defaults['mailer'], MailerInterface::class, true)) {
            throw new Exception('Mailer is not a MailerInterface');
        }

        $this->setDefaults($defaults);

        if (!$this->mailer) {
            throw new Exception('No Mailer');
        }
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
            throw new Exception([
                'Mailer must be a subclass of MailerInterface',
                'solutions' => [
                    'You need to specify a Mailer which implements MailerInterface',
                ],
            ]);
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

    /**
     * @throws Exception
     */
    protected function validateOutbox(): void
    {
        if (!$this->_initialized) {
            throw new Exception([
                'Outbox must be initialized first',
                'solutions' => [
                    'if you use outbox with App, outbox must be add to app using method App::add',
                    'if you use outbox without App, you need to call init() before use',
                ],
            ]);
        }
    }

    /**
     * @param Mail $mail
     *
     * @throws Exception
     *
     * @return MailResponse
     */
    public function send(Mail $mail): MailResponse
    {
        $this->validateOutbox();

        $mail->hook('beforeSend');
        $response = $this->mailer->send($mail);
        $mail->hook('afterSend', [$response]);

        return $response;
    }
}
