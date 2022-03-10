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
        $response_model = new MailResponse($mail->persistence);
        $response_entity = $response_model->createEntity();

        try {
            $this->phpmailer->setFrom(
                $mail->ref('from')->get('email'),
                $mail->ref('from')->get('name')
            );

            $this->addAddress(
                $mail,
                'to',
                function ($address): void {
                    $this->phpmailer->addAddress(
                        $address->get('email'),
                        $address->get('name')
                    );
                }
            );
            $this->addAddress(
                $mail,
                'replyto',
                function ($address): void {
                    $this->phpmailer->addReplyTo(
                        $address->get('email'),
                        $address->get('name')
                    );
                }
            );

            $this->addAddress(
                $mail,
                'cc',
                function ($address): void {
                    $this->phpmailer->addCC(
                        $address->get('email'),
                        $address->get('name')
                    );
                }
            );

            $this->addAddress(
                $mail,
                'bcc',
                function ($address): void {
                    $this->phpmailer->addBCC(
                        $address->get('email'),
                        $address->get('name')
                    );
                }
            );

            $this->phpmailer->Subject = $mail->get('subject');
            $this->phpmailer->Body = $mail->get('html');
            $this->phpmailer->AltBody = $mail->get('text');

            foreach ($mail->ref('headers')->getIterator() as $model) {
                $this->phpmailer->addCustomHeader(
                    $model->get('name'),
                    $model->get('value')
                );
            }

            foreach ($mail->ref('attachments')->getIterator() as $model) {
                $this->phpmailer->addAttachment(
                    $model->get('path'),
                    $model->get('name'),
                    $model->get('encoding'),
                    $model->get('mime'),
                    $model->get('disposition')
                );
            }

            $mail->set('status', Mail::STATUS_SENDING);
            $mail->save();

            if (!$this->phpmailer->send()) {
                throw new \Exception($this->phpmailer->ErrorInfo, 500);
            }

            $mail->set('status', Mail::STATUS_SENT);
            $mail->save();

            // save successful MailResponse
            $response_entity->save(['email_id' => $mail->id]);
        } catch (\Throwable $exception) {
            $mail->set('status', Mail::STATUS_ERROR);
            $mail->save();

            // save unsuccessful MailResponse
            $response_entity->save([
                'email_id' => $mail->getId(),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $response_entity;
    }

    private function addAddress(Mail $mail, string $ref_name, callable $func): void
    {
        foreach ($mail->ref($ref_name)->getIterator() as $id => $address) {
            $func($address);
        }
    }
}
