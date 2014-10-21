<?php

namespace App;

use Nette\Diagnostics\Debugger,
    Nette\Application\UI\Form,
    SkautIS\SkautIS;

/**
 * @author sinacek
 */
class TestPresenter extends BasePresenter {

    public $wsdl;

    //protected $service;

    protected function startup() {
        parent::startup();
//        if (!$this->user->isLoggedIn()) {
//            $this->backlink = $this->storeRequest('+ 3 days');
//            $e = new \SkautIS\Exception\AuthenticationException("Pro testování musíte být přihlášeni do testovacího SkautISu.");
//            $e->backlink = $this->backlink;
//            throw $e;
//        }

        $post = $this->request->post;
        if (isset($post['skautIS_Token'])) {
            $this->context->skautis->init($post);
        }
        $this->template->skautIsAppId = $this->context->skautis->getAppId();
//        if (!$this->context->skautis->isLoggedIn()) {// && $this->action != "default"
//            $this->flashMessage("Chybí aktivní přihlášení do skautISu", "fail");
//            $this->redirect("default");
//        }
        $this->wsdl = $this->context->skautis->getWsdlList();
    }

    public function renderDefault() {
        $info = $this->context->userService->getInfo();
        if ($this->user->isLoggedIn()) {
            $user = $this->context->userService->getUserDetail();
            $info["ID_User"] = $user->ID;
            $info["ID_Person"] = $user->ID_Person;
        }
        $this->template->info = Debugger::dump($info, TRUE);
        $this->template->request = Debugger::dump($this->session->getSection("sisTest")->request, TRUE);
        $this->template->response = Debugger::dump($this->session->getSection("sisTest")->response, TRUE);
    }

    public function createComponentTestForm($name) {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class("aja");
        $form->addSelect("wsdl", "WSDL", $this->wsdl)
                ->addRule(Form::FILLED, "Musís vybrat WSDL")
                ->setDefaultValue("OrganizationUnit");
        $form->addText("service", "Funkce")
                ->setDefaultValue("unitAll")
                ->addRule(FORM::FILLED, "Vypln service");
        $form->addText("cover", "Obal", 40)
                ->getControlPrototype()
                ->placeholder("Alternativní obal požadavku");
        $form->addTextArea("args", "Parametry", 40, 13)
                ->setDefaultValue("ID_UnitParent : 24404")
                ->getControlPrototype()->setClass("input-xlarge");

        $form->addSubmit('send', 'Odeslat')
                ->getControlPrototype()->setClass("btn btn-primary");
        $form->onSuccess[] = array($this, $name . 'Submitted');

        $sess = $this->session->getSection("sisTest");

        if (isset($sess->defaults) && is_array($sess->defaults)) {
            $form->setDefaults($sess->defaults);
        }
        return $form;
    }

    public function testFormSubmitted(Form $form) {
        $sess = $this->session->getSection("sisTest");

        $values = $form->getValues();
//        if (!$this->context->skautis->isLoggedIn()) {
//            $this->flashMessage("Nemáte platné přihlášení do skautISu.", "fail");
//            $this->redirect(":Auth:");
//        }
        $sess->defaults = $values;

        $args = \Nette\Neon\Neon::decode($values['args']);
        if ($args instanceof Traversable) {
            foreach ($args as $key => $value) {
                if ($value instanceof DateTime) {
                    $args[$key] = $value->format("c");
                }
            }
        }
//        $args["StartDate"] = "2012-02-01T00:00:00";
//        $args["EndDate"] = "2012-02-05T00:00:00";
        //dump($args);die();
        $cover = trim($values['cover']);
        if ($cover == "") {
            $cover = NULL;
        }
        $sess->request = $this->prepareArgs(array($args, $cover), $values["service"]);
        try {
            $ret = $this->context->skautis->{$values['wsdl']}->{$values["service"]}($args, $cover);
        } catch (\Exception $e) {
//            dump($e);
            $this->flashMessage($e->getMessage(), "fail");
            $sess->response = $e->getMessage();
            $this->redirect("this");
        }

        $sess->response = $ret;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->invalidateControl('flash');
            $this->invalidateControl('form');
            $this->invalidateControl('testResponse');
        }
    }

    protected function prepareArgs($arguments, $function_name) {
        if (!isset($arguments[0]) || !is_array($arguments[0])) {
            $arguments[0] = array();
        }
        $args = array_merge(
                array(
            SkautIS::APP_ID => $this->context->skautis->getAppId(),
                //SkautIS::TOKEN => $this->context->skautis->getToken(),
                ), $arguments[0]); //k argumentum připoji vlastni informace o aplikaci a uzivateli

        if (isset($arguments[1]) && $arguments[1] !== null) {//pokud je zadan druhy parametr tak lze prejmenovat obal dat
            $matches = array_reverse(preg_split('~/~', $arguments[1])); //rozdeli to na stringy podle /
            $matches[] = 0; //zakladni obal 0=>...
            foreach ($matches as $value) {
                $args = array($value => $args);
            }
        } else {
            $function_name = strtolower(substr($function_name, 0, 1)) . substr($function_name, 1); //nahrazuje lcfirst
            $args = array(array($function_name . "Input" => $args));
        }
        return $args;
    }

}
