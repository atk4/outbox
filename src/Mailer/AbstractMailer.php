<?php

declare(strict_types=1);

namespace Atk4\Outbox\Mailer;

use Atk4\Core\DiContainerTrait;
use Atk4\Outbox\MailerInterface;
use Atk4\Outbox\Model\Mail;
use Atk4\Outbox\Model\MailResponse;
use PHPMailer\PHPMailer\PHPMailer;

abstract class AbstractMailer implements MailerInterface
{
    use DIContainerTrait;

    protected PHPMailer $phpmailer;

    protected string $charset = PHPMailer::CHARSET_UTF8;

    public function __construct(array $defaults = [])
    {
        $this->setDefaults($defaults);
        $this->phpmailer = new PHPMailer(true);
        $this->phpmailer->CharSet = $this->charset;
    }

    public function send(Mail $mail): MailResponse
    {
        $mailResponse = new MailResponse($mail->getPersistence());
        $response_entity = $mailResponse->createEntity();

        try {
            $this->phpmailer->setFrom(
                $mail->get('from')[0]['email'],
                $mail->get('from')[0]['name']
            );

            $this->phpmailer->isHTML(true);

            $this->addAddress(
                $mail,
                'to',
                function ($address): void {
                    $this->phpmailer->addAddress(
                        $address['email'],
                        $address['name']
                    );
                }
            );
            $this->addAddress(
                $mail,
                'replyto',
                function ($address): void {
                    $this->phpmailer->addReplyTo(
                        $address['email'],
                        $address['name']
                    );
                }
            );

            $this->addAddress(
                $mail,
                'cc',
                function ($address): void {
                    $this->phpmailer->addCC(
                        $address['email'],
                        $address['name']
                    );
                }
            );

            $this->addAddress(
                $mail,
                'bcc',
                function ($address): void {
                    $this->phpmailer->addBCC(
                        $address['email'],
                        $address['name']
                    );
                }
            );

            $this->phpmailer->Subject = $mail->get('subject');
            $this->phpmailer->Body = $mail->get('html');
            $this->phpmailer->AltBody = $mail->get('text');

            foreach ($mail->get('headers') ?? [] as $model) {
                $this->phpmailer->addCustomHeader(
                    $model['name'],
                    $model['value']
                );
            }

            foreach ($mail->get('attachments') ?? [] as $model) {
                $this->phpmailer->addAttachment(
                    $model['path'],
                    $model['name'],
                    $model['encoding'],
                    $model['mime'],
                    $model['disposition']
                );
            }

            $mail->set('status', Mail::STATUS_SENDING);
            $mail->save();

            if (!$this->phpmailer->send()) {
                throw new \Exception($this->phpmailer->ErrorInfo, 400);
            }

            $mail->set('status', Mail::STATUS_SENT);
            $mail->save();

            // save successful MailResponse
            $response_entity->save(['email_id' => $mail->id]);
        } catch (\Throwable $throwable) {
            $mail->set('status', Mail::STATUS_ERROR);
            $mail->save();

            // save unsuccessful MailResponse
            $response_entity->save([
                'email_id' => $mail->getId(),
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }

        return $response_entity;
    }

    private function addAddress(Mail $mail, string $ref_name, callable $func): void
    {
        foreach ($mail->get($ref_name) ?? [] as $address) {
            $func($address);
        }
    }
}
