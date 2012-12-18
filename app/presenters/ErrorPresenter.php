<?php

class ErrorPresenter extends Presenter {

    public function renderDefault($exception) {
        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->payload->error = TRUE;
            $this->terminate();
        } elseif ($exception instanceof BadRequestException) {
            $code = $exception->getCode();
            $this->setView(in_array($code, array(403, 404, 405, 410, 500)) ? $code : '4xx'); // load template 403.latte or 404.latte or ... 4xx.latte
        } elseif ($exception instanceof SkautIS_Exception) {
            Debugger::log($exception, Debugger::WARNING); // and log exception
            $this->setView('SkautIS');
            $this->template->ex = $exception;
        } elseif ($exception instanceof SkautIS_AuthenticationException) {//vypršelo přihlášení do SkautISu
            $this->user->logout(TRUE);
            $this->flashMessage($exception->getMessage() != "" ? $exception->getMessage() : "Vypršelo přihlášení do SkautISu" , "danger");
            $backlink = $exception->backlink ? $exception->backlink : NULL;
            $this->redirect(":Default:", array("backlink" => $backlink));
        } else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::ERROR); // and log exception
        }
    }

}
