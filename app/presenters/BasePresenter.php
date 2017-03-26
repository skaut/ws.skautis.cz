<?php

namespace App;

use Nette;
use WebLoader;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /**
     * backlink
     */
    protected $backlink;

    /**
     *
     * @var string
     */
    protected $webtempUrl;

    /**
     *
     * @var \UserService
     */
    protected $userService;

    public function injectUserService(\UserService $us)
    {
        $this->userService = $us;
    }

    protected function startup()
    {
        parent::startup();
        $this->template->backlink = $this->getParameter("backlink");
        $this->webtempUrl = $this->context->getByType('Nette\Http\Request')->getUrl()->baseUrl . 'webtemp';
    }

    //upravuje roli ve skautISu
    public function handleChangeRole($roleId)
    {
        $this->userService->updateSkautISRole($roleId);
        $this->redirect("this");
    }

    public function beforeRender()
    {
        parent::beforeRender();
        try {
            if ($this->user->isLoggedIn() && $this->userService->isLoggedIn(TRUE)) {
                $this->template->myRoles = $this->userService->getAllSkautISRoles();
                $this->template->myRole = $this->userService->getRoleId();
            } else {
                throw new \Exception("Uživatel by odhlášen");
            }
        } catch (\Exception $ex) {
            $this->user->logout();
        }
    }

    public function createComponentCss()
    {
        $files = new WebLoader\FileCollection(WWW_DIR . '/css');
        $compiler = WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/webtemp');

        $control = new WebLoader\Nette\CssLoader($compiler, $this->webtempUrl);
        $control->setMedia('screen');
        $files->addFiles([
            'bootstrap.min.css',
            'bootstrap-responsive.min.css',
            'jquery-ui-1.8.css',
            'site.css'
        ]);
        return $control;
    }

    public function createComponentJs()
    {
        $files = new WebLoader\FileCollection(WWW_DIR . '/js');
        $compiler = WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/webtemp');
        $files->addFiles([
            'jquery-v1.11.1.js',
            'jquery.ui.min.js',
            'bootstrap.js',
            'combobox.js',
            'nette.ajax.js',
            'netteForms.js',
            'my.js',
        ]);
        return new WebLoader\Nette\JavaScriptLoader($compiler, $this->webtempUrl);
    }

}
