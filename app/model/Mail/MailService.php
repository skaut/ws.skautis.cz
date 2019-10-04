<?php

use Nette\Application\UI\ITemplate;
use Nette\Mail\Message;

class MailService
{
    use Nette\SmartObject;

    public $mailer;

    const EMAIL_SENDER = '"Webové služby" <webove.sluzby@skaut.cz>';

    public function __construct(\Nette\Mail\IMailer $mailer)
    {
        $this->mailer = $mailer;

    }

    public function sendRequest(ITemplate $template, $values)
    {
        $template->setFile(dirname(__FILE__) . "/mail.request.latte");
        $mail = new Message;
        $mail->setHtmlBody($template);
        $mail->setSubject("Žádost o registraci aplikace ve skautISu");
        $mailUstredi = $mail;
        $mailZadatel = $mail;

        $mailZadatel->setFrom(self::EMAIL_SENDER);
        $mailZadatel->addTo($values->email, $values->username);

        $mailUstredi->setFrom($values->email, $values->username);
        $mailUstredi->addTo(self::EMAIL_SENDER);

        return $this->mailer->send($mailUstredi) && $this->send($mailZadatel);
    }

}
