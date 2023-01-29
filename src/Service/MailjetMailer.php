<?php

namespace Firesphere\Mailjet\Service;

use Mailjet\Client;
use Mailjet\Resources;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\Email\Mailer;
use SilverStripe\Core\Environment;

class MailjetMailer implements Mailer
{
    /**
     * @var Client
     */
    private $service;

    /**
     * List of Mailjet-formatted emails
     * @var array
     */
    private $messages;

    /**
     * @var bool
     */
    protected $send;

    /**
     * @var bool
     */
    protected $sandbox;

    public function __construct($send = true, $sandbox = false)
    {
        $this->send = $send;
        $this->sandbox = $sandbox;
        $key = Environment::getEnv('SS_MAILJET_KEY');
        $secret = Environment::getEnv('SS_MAILJET_SECRET');
        $this->service = new Client($key, $secret, $this->send, ['version' => 'v3.1']);
    }

    /**
     * @param array|Email $email
     * @return bool
     */
    public function send($email)
    {
        if (!is_iterable($email)) {
            $email = [$email];
        }
        foreach ($email as $mail) {
            $this->addMail($mail);
        }

        $body = [
            'Messages' => $this->messages
        ];

        if ($this->sandbox) {
            $body['SandboxMode'] = true;
        }

        $result = $this->service->post(
            Resources::$Email,
            [
                'body' => $body
            ]
        );

        return $result->success() === true;
    }

    /**
     * @param Email $mail
     * @return void
     */
    public function addMail($mail)
    {
        $sets = [
            'Cc'      => $mail->getCC(),
            'Bcc'     => $mail->getBCC(),
            'To'      => $mail->getTo(),
            'ReplyTo' => $mail->getReplyTo(),
        ];
        $msg = [
            'From'     => [
                'Email' => array_keys($mail->getFrom())[0],
                'Name'  => array_values($mail->getFrom())[0]
            ],
            'Subject'  => $mail->getSubject(),
            'TextPart' => '',
            'HTMLPart' => $mail->getBody()
        ];
        foreach ($sets as $type => $values) {
            if (!is_array($values) || !count($values)) {
                continue;
            }
            foreach ($values as $email => $name) {
                $msg[$type][] = [
                    'Email' => $email,
                    'Name'  => $name
                ];
            }
        }

        if ($emailsFrom = Email::getSendAllEmailsFrom()) {
            $msg['From'] = [
                'Email' => $emailsFrom,
            ];
        }
        if ($emailsTo = Email::getSendAllEmailsTo()) {
            $msg['To'] = [
                [
                    'Email' => array_keys($emailsTo)[0]
                ]
            ];
        }
        if ($emailsBcc = Email::getBCCAllEmailsTo()) {
            $msg['Bcc']['Email'] = array_merge($msg['Bcc']['Email'], array_keys($emailsBcc));
            $msg['Bcc']['Name'] = array_merge($msg['Bcc']['Name'], array_values($emailsBcc));
        }

        foreach ($mail->getSwiftMessage()->getChildren() as $child) {
            if ($child instanceof \Swift_Attachment) {
                $msg['Inline_attachments'][] = [
                    'content'      => base64_encode($child->getBody()),
                    'Content-type' => $child->getContentType(),
                    'Filename'     => $child->getFilename()
                ];
            }
        }

        $this->messages[] = $msg;
    }

    /**
     * @param bool|mixed $send
     * @return MailjetMailer
     */
    public function setSend($send)
    {
        $this->send = $send;

        return $this;
    }

    /**
     * @param bool|mixed $sandbox
     * @return MailjetMailer
     */
    public function setSandbox($sandbox)
    {
        $this->sandbox = $sandbox;

        return $this;
    }
}