<?php

namespace App;

use Nette\Application\UI\Form;
use Nette\Neon\Neon;
use Skautis\Skautis;
use Skautis\Wsdl\WebServiceName;
use Tracy\Debugger;

/**
 * @author sinacek
 */
class TestPresenter extends BasePresenter
{

    public $wsdl;
    private $skautis;

    public function __construct(Skautis $skautis)
    {
        parent::__construct();
        $this->skautis = $skautis;
    }

    protected function startup()
    {
        parent::startup();

        $post = $this->request->post;
        if (isset($post['skautIS_Token'])) {
            $this->skautis->init($post);
        }
        $this->template->skautIsAppId = $this->skautis->getConfig()->getAppId();
        $this->wsdl = array_values(WebServiceName::getConstants());
    }

    public function renderDefault()
    {
        $info = $this->userService->getInfo();
        if ($this->user->isLoggedIn()) {
            $user = $this->userService->getUserDetail();
            $info["ID_User"] = $user->ID;
            $info["ID_Person"] = $user->ID_Person;
        }
        $this->template->info = Debugger::dump($info, TRUE);
        $this->template->request = Debugger::dump($this->session->getSection("sisTest")->request, TRUE);
        $this->template->response = Debugger::dump($this->session->getSection("sisTest")->response, TRUE);
    }

    public function createComponentTestForm($name)
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class("aja");
        $form->addSelect("wsdl", "WSDL", $this->wsdl)
            ->addRule(Form::FILLED, "Musís vybrat WSDL")
            ->setDefaultValue("12");
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
        $form->onSuccess[] = [$this, $name . 'Submitted'];

        $sess = $this->getSession('sisTest');

        if (isset($sess->defaults) && is_array((array)$sess->defaults)) {
            $form->setDefaults((array)$sess->defaults);
        }
        return $form;
    }

    public function testFormSubmitted(Form $form)
    {
        $sess = $this->getSession('sisTest');
        $values = $form->getValues();
        $sess->defaults = $values;

        $args = Neon::decode($values['args']);
        if ($args instanceof Traversable) {
            foreach ($args as $key => $value) {
                if ($value instanceof DateTime) {
                    $args[$key] = $value->format("c");
                }
            }
        }
        $cover = trim($values['cover']);
        if ($cover == "") {
            $cover = NULL;
        }
        $sess->request = $this->prepareArgs([$args, $cover], $values["service"]);
        try {
            $ret = $this->skautis->{$this->wsdl[$values['wsdl']]}->{$values["service"]}($args, $cover);
        } catch (\Exception $e) {
            $this->flashMessage($e->getMessage(), "fail");
            $sess->response = $e->getMessage();
            $this->redirect("this");
        }

        $sess->response = $ret;

        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('flash');
            $this->redrawControl('form');
            $this->redrawControl('testResponse');
        }
    }

    protected function prepareArgs($arguments, $function_name)
    {
        if (!isset($arguments[0]) || !is_array($arguments[0])) {
            $arguments[0] = [];
        }
        if (!array_key_exists("ID_Application", $arguments[0])) {
            $arguments[0]["ID_Application"] = $this->skautis->getConfig()->getAppId();
        }
        $args = $arguments[0];

        if (isset($arguments[1]) && $arguments[1] !== NULL) {//pokud je zadan druhy parametr tak lze prejmenovat obal dat
            $matches = array_reverse(preg_split('~/~', $arguments[1])); //rozdeli to na stringy podle /
            $matches[] = 0; //zakladni obal 0=>...
            foreach ($matches as $value) {
                $args = [$value => $args];
            }
        } else {
            $function_name = strtolower(substr($function_name, 0, 1)) . substr($function_name, 1); //nahrazuje lcfirst
            $args = [[$function_name . "Input" => $args]];
        }
        return $args;
    }

}
