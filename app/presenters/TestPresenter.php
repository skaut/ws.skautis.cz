<?php

/**
 * @author sinacek
 */
class TestPresenter extends BasePresenter {

    public $wsdl;
    protected $service;

    protected function startup() {
        parent::startup();
        if (!$this->user->isLoggedIn()) {
            $this->backlink = $this->storeRequest('+ 3 days');
            throw new SkautIS_AuthenticationException("Pro testování musíte být přihlášeni do testovacího SkautISu.", $this->backlink);
        }

        $post = $this->request->post;
        if (isset($post['skautIS_Token'])) {
            $this->context->skautIS->init($post);
        }
        $this->template->skautIsAppId = $this->context->skautIS->getAppId();
        if (!$this->context->skautIS->isLoggedIn() && $this->action != "default") {
            $this->accessFail();
            $this->flashMessage("Chybí aktivní přihlášení do skautISu", "fail");
            $this->redirect("default");
        }
        $this->wsdl = $this->context->skautIS->getWsdlList();
    }

    public function renderDefault() {
        $this->template->request = Debugger::dump($this->session->getSection("sisTest")->request, TRUE);
        $this->template->response = Debugger::dump($this->session->getSection("sisTest")->response, TRUE);
    }

    public function createComponentTestForm($name) {
        $form = new AppForm($this, $name);
        $form->getElementPrototype()->class("aja");
        $form->addSelect("wsdl", "WSDL", $this->wsdl)
                ->addRule(Form::FILLED, "Musís vybrat WSDL");
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
        if (isset($sess->defaults))
            $form->setDefaults($sess->defaults);
        return $form;
    }

    public function testFormSubmitted(AppForm $form) {
        $sess = &$this->session->getSection("sisTest");

        $values = $form->getValues();
        if (!$this->context->skautIS->isLoggedIn()) {
            $this->flashMessage("Nemáte platné přihlášení do skautISu.", "fail");
            $this->redirect(":Auth:");
        }
        $sess->defaults = $values;

        $args = Neon::decode($values['args']);

        //dump($args);die();
        $cover = trim($values['cover']);
        if ($cover == "")
            $cover = NULL;
        $sess->request = $this->prepareArgs(array($args, $cover), $values["service"]);
        try {
            $ret = $this->context->skautIS->{$values['wsdl']}->{$values["service"]}($args, $cover);
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), "fail");
            $sess->response = $e->getMessage();
            $this->redirect("this");
        }
        $sess->response = $ret;

        if (!$this->isAjax())
            $this->redirect('this');
        else {
            $this->invalidateControl('flash');
            $this->invalidateControl('form');
            $this->invalidateControl('testResponse');
        }
    }
    
    protected function prepareArgs($arguments, $function_name){
        if (!isset($arguments[0]) || !is_array($arguments[0])) {
            $arguments[0] = array();
        }
        $args = array_merge($this->context->skautIS->getStorage()->init, $arguments[0]); //k argumentum připoji vlastni informace o aplikaci a uzivateli

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
