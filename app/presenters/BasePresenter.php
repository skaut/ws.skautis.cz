<?php

abstract class BasePresenter extends Nette\Application\UI\Presenter {

    /**
     * backlink
     */
    protected $backlink;

    protected function startup() {
        parent::startup();
        Extras\Debug\RequestsPanel::register();
        $this->template->backlink = $this->getParameter("backlink");
        $storage = \Nette\Environment::getSession()->getSection("__" . __CLASS__);
        $this->context->skautIS->setStorage($storage, TRUE);
//        if ($this->user->isLoggedIn()) //prodluzuje přihlášení při každém požadavku
//            $this->context->authService->updateLogoutTime();
    }

    //upravuje roli ve skautISu
    public function handleChangeRole($roleId) {
        $this->context->userService->updateSkautISRole($roleId);
        $this->redirect("this");
    }

    public function beforeRender() {
        parent::beforeRender();
        if ($this->user->isLoggedIn() && $this->context->userService->isLoggedIn()) {
            $this->template->myRoles = $this->context->userService->getAllSkautISRoles();
            $this->template->myRole = $this->context->userService->getRoleId();
        }
    }

    public function createComponentCss() {
        $files = new WebLoader\FileCollection(WWW_DIR . '/css');
        $compiler = WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/webtemp');

        //s minimalizací zlobí bootstrap
//        $compiler->addFilter(new VariablesFilter(array('foo' => 'bar')));        
//        function mini($code) {
//            return CssMin::minify($code);
//        }
//        $compiler->addFilter("mini");
        $control = new WebLoader\Nette\CssLoader($compiler, $this->context->httpRequest->url->baseUrl . 'webtemp');
        $control->setMedia('screen');

        return $control;
    }

    public function createComponentJs() {
        $files = new WebLoader\FileCollection(WWW_DIR . '/js');
        $compiler = WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/webtemp');
        return new WebLoader\Nette\JavaScriptLoader($compiler, $this->context->httpRequest->url->baseUrl . 'webtemp');
    }

}
