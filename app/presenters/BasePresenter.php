<?php

namespace App;

use Nette,
    WebLoader;

abstract class BasePresenter extends Nette\Application\UI\Presenter {

    /**
     * backlink
     */
    protected $backlink;

    protected function startup() {
        parent::startup();
        $this->template->backlink = $this->getParameter("backlink");
        
        $storage = $this->context->getByType('Nette\Http\Session')->getSection("__" . __CLASS__);
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
        try {
            if ($this->user->isLoggedIn() && $this->context->userService->isLoggedIn()) {
                $this->template->myRoles = $this->context->userService->getAllSkautISRoles();
                $this->template->myRole = $this->context->userService->getRoleId();
            }
        } catch (Exception $ex) {
            $this->user->logout();
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
        $files->addFiles(array(
            'bootstrap.min.css',
            'bootstrap-responsive.min.css',
            'jquery-ui-1.8.css',
            'site.css'
        ));
        return $control;
    }

    public function createComponentJs() {
        $files = new WebLoader\FileCollection(WWW_DIR . '/js');
        $compiler = WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/webtemp');
        $files->addFiles(array(
            'jquery-v1.11.1.js',
            'jquery.ui.min.js',
            'bootstrap.js',
            'combobox.js',
            'nette.ajax.js',
            'netteForms.js',
            'my.js',
        ));
        return new WebLoader\Nette\JavaScriptLoader($compiler, $this->context->httpRequest->url->baseUrl . 'webtemp');
    }

}
