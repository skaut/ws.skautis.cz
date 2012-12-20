<?php

abstract class BasePresenter extends Presenter {

    /**
     * backlink
     */
    protected $backlink;

    protected function startup() {
        parent::startup();
        RequestsPanel::register();
        $this->template->backlink = $this->getParameter("backlink");
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
        $files = new FileCollection(WWW_DIR . '/css');
        $compiler = Compiler::createCssCompiler($files, WWW_DIR . '/webtemp');

        //s minimalizací zlobí bootstrap
//        $compiler->addFilter(new VariablesFilter(array('foo' => 'bar')));        
//        function mini($code) {
//            return CssMin::minify($code);
//        }
//        $compiler->addFilter("mini");
        $control = new CssLoader($compiler, $this->context->httpRequest->url->baseUrl . 'webtemp');
        $control->setMedia('screen');

        return $control;
    }

    public function createComponentJs() {
        $files = new FileCollection(WWW_DIR . '/js');
        $compiler = Compiler::createJsCompiler($files, WWW_DIR . '/webtemp');
        return new JavaScriptLoader($compiler, $this->context->httpRequest->url->baseUrl . 'webtemp');
    }

}