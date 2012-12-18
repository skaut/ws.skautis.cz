<?php

/**
 * @author sinacek
 */
class AppRequestPresenter extends BasePresenter {
    
    const WS_MAIL = '"Webové služby" <webove.sluzby@skaut.cz>';


    protected $wsdl = array(
        "appMng" => array("url"=> "ApplicationManagement", "label"=>"Webová služba pro správu přístupů externích aplikací"),
        "evaluation" => array("url"=> "Evaluation", "label"=>"Webová služba pro práci s hodnocením kvality"),
        "events" => array("url"=> "Events", "label"=>" Webová služba pro práci s akcemi (sněmy apod.)"),
        "exports" => array("url"=> "Exports", "label"=>"Webová služba pro export dat do jiných systémů"),
        "googleApps" => array("url"=> "GoogleApps", "label"=>"Webová služba pro práci s GoogleApps (zápis dat do databáze, komunikace s GoogleApps)"),
        "journal" => array("url"=> "Journal", "label"=>"Webová služba pro práci s časopisy a fakturami"),
        "msg" => array("url"=> "Message", "label"=>"Interní zpravodajský systém"),
        "org" => array("url"=> "OrganizationUnit", "label"=>"Webová služba pro práci s organizačními jednotkami a osobami"),
        "reports" => array("url"=> "Reports", "label"=>"Generování tiskových sestav"),
        "summary" => array("url"=> "Summary", "label"=>"Exporty/přehledy"),
        "telephony" => array("url"=> "Telephony", "label"=>"Skautská telefonní síť"),
        "user" => array("url"=> "UserManagement", "label"=>"Webová služba pro práci s uživateli (zakládání, přidělování rolí, přihlašování apod.)"),
        "welcome" => array("url"=> "Welcome", "label"=>"Webová služba pro práci s uvítacími balíčky"),
    );
    
    protected function startup() {
        parent::startup();
        $this->template->wsdl = $this->wsdl;
        $this->template->names = array();
    }

    public function actionDefault() {
        foreach ($this->wsdl as $key => $data) {
            $this->template->names[$key] = $this->getFunctionNames("http://test-is.skaut.cz/JunakWebservice/".$data['url'].".asmx?WSDL");
        }
    }

    protected function prepareContainer(&$form, $containerName, $names) {
        $gEvent = $form->addContainer($containerName);
        foreach ($names as $val) {
            $gEvent->addCheckbox($val, $val);
        }
    }

    public function createComponentAddForm($name) {
        $form = new AppForm($this, $name);
        $form->addText("name", "Název aplikace")
                ->addRule(Form::FILLED, "Zadej název aplikace");
        $form->addText("desc", "Popis aplikace")
                ->addRule(Form::FILLED, "Zadej popis aplikace");
        $form->addCheckbox("isTest", "Testovací režim?")
                ->setDefaultValue("TRUE");
        $form->addText("username", "Jméno a příjmení")
                ->addRule(Form::FILLED, "Zadejte jméno a příjmení");
        $form->addText("nick", "Přezdívka");
        $form->addText("email", "Kontaktní email")
                ->addRule(Form::EMAIL, "Zadejte email");
        $form->addText("orgNum", "Reg. číslo jednotky");
        $form->addText("urlBase", "URL aplikace")
                ->addRule(Form::URL, "Zadej platnou URL aplikace");
        $form->addText("urlLogin", "URL po přihlášení")
                ->addRule(Form::URL, "Zadej platnou  URL po přihlášení");
        $form->addText("urlLogout", "URL po odhlášení")
                ->addRule(Form::URL, "Zadej platnou URL po odhlášení");
        $form->addText("urlInfo", "URL informační stránky");
        $form->addText("ip", "IP adresa serveru");
        $form->addTextArea("note", "Poznámka", 40, 5)
                ->getControlPrototype()->setClass("input-xlarge");
        
        foreach ($this->wsdl as $key => $v){
            $this->prepareContainer($form, $key, $this->template->names[$key]);
        }        

        $form->addSubmit('send', 'Odeslat')
                ->getControlPrototype()->setClass("btn btn-primary");
        $form->onSuccess[] = array($this, $name . 'Submitted');

//        $sess = $this->session->getSection("sisTest");
//        if (isset($sess->defaults))
//            $form->setDefaults($sess->defaults);
        return $form;
    }

    public function addFormSubmitted(AppForm $form) {
        $values = $form->values;
        
        foreach ($this->wsdl as $key => $value) {//ziska zakrtnute pole
            $tmp = array();
            foreach ($values[$key] as $fid => $fval) {
                if($fval){
                    $tmp[] = $fid;
                }
            }
            $values[$key] = $tmp;
        }
        
        $template = $this->template;
        $template->setFile(dirname(__FILE__) . '/../templates/AppRequest/mail.request.latte');
        $template->registerFilter(new LatteFilter);
        $template->values = $values;

        $mail = new Mail;
        $mail->setHtmlBody($template);
        $mail->setSubject("Žádost o registraci aplikace ve skautISu");
        $mailUstredi = $mail;
        $mailZadatel = $mail;
        
        $mailZadatel->setFrom(self::WS_MAIL);
        $mailZadatel->addTo($values->email, $values->nickname);
        $mailZadatel->send();
        
        $mailUstredi->setFrom($values->email, $values->nickname);
        $mailUstredi->addTo(self::WS_MAIL);
        $mailUstredi->send();
        
//        $this->flashMessage("Odeslani emailu je vypnuté!", "danger");
        $this->presenter->flashMessage("Žádost byla odeslána na ústředí a na zadaný kontaktní email.");
        $this->presenter->redirect("default");
    }

    public function getFunctionNames($url) {
        $client = new SoapClient($url);
        $functions = $client->__getFunctions();

        if(!function_exists("getFName")){
            function getFName($n) {
                $tmp = preg_split("/[\s()]+/", $n);
                return $tmp[1];
            }
        }

        $ret = array_unique(array_map("getFName", $functions));
        sort($ret);
        return $ret;
    }

}