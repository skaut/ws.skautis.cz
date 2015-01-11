<?php

use Nette\Mail\Message,
    \Nette\Mail\SendmailMailer;

/**
 * @author Hána František
 */
class MailService extends BaseService {

    protected $sendEmail;

    const EMAIL_SENDER = '"Webové služby" <webove.sluzby@skaut.cz>';

    public function __construct(Skautis\Skautis $skautis = NULL, $sendEmail = FALSE) {
        parent::__construct($skautis);
        $this->sendEmail = $sendEmail;
    }

    private function send(Message $mail) {
        if ($this->sendEmail) {
            $mailer = new SendmailMailer();
            $mailer->send($mail);
            return TRUE;
        } else {
            echo $mail->getHtmlBody() . "<hr>";
            die();
        }
    }

    public function sendRequest(\Nette\Application\UI\ITemplate $template, $values) {
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

        return $this->send($mailUstredi) && $this->send($mailZadatel);
    }

}
