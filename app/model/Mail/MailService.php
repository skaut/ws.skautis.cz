<?php

declare(strict_types=1);

namespace Model;

use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

use Nette\Utils\ArrayHash;
use function dirname;

class MailService
{
    public IMailer $mailer;

    public const EMAIL_SENDER = '"Webové služby" <webove.sluzby@skaut.cz>';

    private LinkGenerator $linkGenerator;

    private ITemplateFactory $templateFactory;

    public function __construct(
        IMailer $mailer,
        LinkGenerator $linkGenerator,
        ITemplateFactory $templateFactory
    )
    {
        $this->mailer = $mailer;
        $this->linkGenerator = $linkGenerator;
        $this->templateFactory = $templateFactory;
    }

    private function createTemplate()
    {
        $template = $this->templateFactory->createTemplate();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        return $template;
    }

    /** @param mixed[] $values */
    public function sendRequest(ArrayHash $values): void
    {
        $template = $this->createTemplate();
        $nameZadatel = $values->lastName . " ". $values->firstName;
        $nameZadatel .= $values->nick !== "" ? " (".$values->nick.")" : "";
        $html = $template->renderToString(dirname(__FILE__) . '/mail.request.latte', ['values'=>$values, 'nameZadatel'=>$nameZadatel]);

        $mail = new Message();
        $mail->setHtmlBody($html);
        $mail->setSubject('Žádost o registraci aplikace ve skautISu');
        $mailUstredi = $mail;
        $mailZadatel = $mail;

        $mailZadatel->setFrom(self::EMAIL_SENDER);
        $mailZadatel->addTo($values->email, $nameZadatel);

        $mailUstredi->setFrom($values->email, $nameZadatel);
        $mailUstredi->addTo(self::EMAIL_SENDER);

        $this->mailer->send($mailUstredi) && $this->mailer->send($mailZadatel);
    }
}
