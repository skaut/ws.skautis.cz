<?php

declare(strict_types=1);

namespace App;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Skautis\Exception;
use Skautis\Wsdl\AuthenticationException;
use Tracy\Debugger;

use function in_array;

class ErrorPresenter extends Presenter
{
    /**
     * @param mixed $exception
     */
    public function renderDefault($exception): void
    {
        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->payload->error = true;
            $this->terminate();
        } elseif ($exception instanceof BadRequestException) {
            $code = $exception->getCode();
            $this->setView(in_array($code, [403, 404, 405, 410, 500]) ? $code : '4xx'); // load template 403.latte or 404.latte or ... 4xx.latte
        } elseif ($exception instanceof AuthenticationException) {//vypršelo přihlášení do SkautISu
            $this->user->logout(true);
            $this->flashMessage($exception->getMessage() !== '' ? $exception->getMessage() : 'Vypršelo přihlášení do SkautISu', 'danger');
            $backlink = $exception->backlink ?? null;
            $this->redirect(':Default:', ['backlink' => $backlink]);
        } elseif ($exception instanceof Exception) {
            $this->setView('SkautIS');
            $this->template->ex = $exception;
        } else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::EXCEPTION); // and log exception
        }
    }
}
