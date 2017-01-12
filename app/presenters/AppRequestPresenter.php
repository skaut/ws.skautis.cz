<?php

namespace App;

use Nette\Application\UI\Form;

/**
 * @author sinacek
 */
class AppRequestPresenter extends BasePresenter {

    protected $wsdl = array(
        "appMng" => array("url" => "ApplicationManagement", "label" => "Webová služba pro správu přístupů externích aplikací"),
        "content" => array("url" => "ContentManagement", "label" => "Webová služba pro správu obsahu (redakční systém)."),
        "evaluation" => array("url" => "Evaluation", "label" => "Webová služba pro práci s hodnocením kvality"),
        "events" => array("url" => "Events", "label" => " Webová služba pro práci s akcemi (sněmy apod.)"),
        "exports" => array("url" => "Exports", "label" => "Webová služba pro export dat do jiných systémů"),
        "googleApps" => array("url" => "GoogleApps", "label" => "Webová služba pro práci s GoogleApps (zápis dat do databáze, komunikace s GoogleApps)"),
        "grants" => array("url" => "Grants", "label" => "Webová služba pro práci s dotacemi"),
        "journal" => array("url" => "Journal", "label" => "Webová služba pro práci s časopisy a fakturami"),
        "material" => array("url" => "Material", "label" => "Webová služba pro práci s materiálem a sklady"),
        "msg" => array("url" => "Message", "label" => "Interní zpravodajský systém"),
        "org" => array("url" => "OrganizationUnit", "label" => "Webová služba pro práci s organizačními jednotkami a osobami"),
        "power" => array("url" => "Power", "label" => "Skautská energie"),
        "reports" => array("url" => "Reports", "label" => "Generování tiskových sestav"),
        "summary" => array("url" => "Summary", "label" => "Exporty/přehledy"),
        "task" => array("url" => "Task", "label" => "Úkoly ve skautISu"),
        "telephony" => array("url" => "Telephony", "label" => "Skautská telefonní síť"),
        "user" => array("url" => "UserManagement", "label" => "Webová služba pro práci s uživateli (zakládání, přidělování rolí, přihlašování apod.)"),
        //"vivant" => array("url" => "Vivant", "label" => "Webová služba pro exporty dat pro Vivant"), //slovenský skautis
        "welcome" => array("url" => "Welcome", "label" => "Webová služba pro práci s uvítacími balíčky"),
    );
    protected $generalGroups = array(
        "ggAll" => "Hledání záznamů (...All)",
        "ggDetail" => "Načtení detailních informací (...Detail)",
        "ggInsert" => "Založení nového záznamu (...Insert)",
        "ggEdit" => "Editace záznamů (...Edit)",
        "ggDelete" => "Smazání záznamu (...Delete)",
        "ggReport" => "Generování tiskových sestav (Report)",
        "ggOther" => "Ostatní funkce",
    );
    
    /**
     *
     * @var \MailService
     */
    protected $mailService;
    
    public function __construct(\MailService $ms) {
        parent::__construct();
        $this->mailService = $ms;
    }

    protected function startup() {
        parent::startup();
        $this->template->wsdl = $this->wsdl;
        $this->template->generalGroups = $this->generalGroups;
        $this->template->names = array();
    }

//    public function actionDefault() {
//        foreach ($this->wsdl as $key => $data) {
//            $this->template->names[$key] = $this->getFunctionNames("http://test-is.skaut.cz/JunakWebservice/" . $data['url'] . ".asmx?WSDL");
//        }
//    }

    protected function prepareContainer(&$form, $containerName, $names) {
        $gEvent = $form->addContainer($containerName);
        foreach ($names as $val) {
            $gEvent->addCheckbox($val, $val);
        }
    }

    public function createComponentAddForm($name) {
        $form = new Form($this, $name);
        $form->addText("name", "Název aplikace")
                ->addRule(Form::FILLED, "Zadej název aplikace");
        $form->addText("desc", "Popis aplikace")
                ->addRule(Form::FILLED, "Zadej popis aplikace");
//        $form->addCheckbox("isTest", "Testovací režim?")
//                ->setDefaultValue("TRUE");
        $form->addText("username", "Jméno a příjmení")
                ->addRule(Form::FILLED, "Zadejte jméno a příjmení");
        $form->addText("nick", "Přezdívka");
        $form->addText("email", "Kontaktní email")
                ->addRule(Form::FILLED, "Zadejte email")
                ->addRule(Form::EMAIL, "Zadejte platný email");
        $form->addText("orgNum", "Reg. číslo jednotky");
        $form->addText("urlBase", "URL aplikace")
                ->setDefaultValue("http://")
                ->addRule(Form::URL, "Zadej platnou URL aplikace");
        $form->addText("urlLogin", "URL po přihlášení")
                ->setDefaultValue("http://")
                ->addRule(Form::URL, "Zadej platnou  URL po přihlášení");
        $form->addText("urlLogout", "URL po odhlášení")
                ->setDefaultValue("http://")
                ->addRule(Form::URL, "Zadej platnou URL po odhlášení");
//        $form->addText("urlInfo", "URL informační stránky")
//                ->setDefaultValue("http://");
//        $form->addText("ip", "IP adresa serveru");
        $form->addTextArea("note", "Poznámka", 40, 5)
                ->getControlPrototype()->setClass("input-xlarge");

//        $gg = $form->addContainer("generalGroups");
//        foreach ($this->generalGroups as $id => $val) {
//            $gg->addCheckbox($id, $val);
//        }
//
//        foreach ($this->wsdl as $key => $v) {
//            $this->prepareContainer($form, $key, $this->template->names[$key]);
//        }

        $form->addSubmit('send', 'Odeslat')
                ->getControlPrototype()->setClass("btn btn-primary");
        $form->onSuccess[] = array($this, $name . 'Submitted');

//        $sess = $this->session->getSection("sisTest");
//        if (isset($sess->defaults))
//            $form->setDefaults($sess->defaults);
        return $form;
    }

    public function addFormSubmitted(Form $form) {
        $values = $form->values;
//        dump($values);
        //balicky sluzeb
//        $tmpGG = array();
//        foreach ($values["generalGroups"] as $ggid => $gval) {
//            if ($gval) {
//                $tmpGG[] = $ggid;
//            }
//        }
//        $values["generalGroups"] = $tmpGG;
// 
//        foreach ($this->wsdl as $key => $value) {//ziska zakrtnute pole
//            $tmp = array();
//            foreach ($values[$key] as $fid => $fval) {
//                if ($fval)
//                    $tmp[] = $fid;
//            }
//            $values[$key] = $tmp;
//        }
        $template = $this->template;
        $template->values = $values;
        $this->mailService->sendRequest($template, $values);
        $this->presenter->flashMessage("Žádost byla odeslána na ústředí a na zadaný kontaktní email.");
        $this->presenter->redirect("default");
    }

//    public function getFunctionNames($url) {
//        $client = new \SoapClient($url);
//        $functions = $client->__getFunctions();
//
//
//        if (!function_exists(__NAMESPACE__ . "\getfname")) {
//
//            function getFName($n) {
//                $tmp = preg_split("/[\s()]+/", $n);
//                return $tmp[1];
//            }
//
//        }
//
//        $ret = array_unique(array_map("getFName", $functions));
//        sort($ret);
//        return $ret;
//    }
}
