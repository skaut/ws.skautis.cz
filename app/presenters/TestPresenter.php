<?php

declare(strict_types=1);

namespace App;

use Nette\Application\UI\Form;
use Nette\Neon\Neon;
use Skautis\Skautis;
use Throwable;
use Tracy\Debugger;

use function array_key_exists;
use function array_reverse;
use function is_array;
use function preg_split;
use function strtolower;
use function substr;
use function trim;

class TestPresenter extends BasePresenter
{
    private const SESSION_NAMESPACE = 'sisTest';

    /** @var string[] */
    public array $wsdl;

    private Skautis $skautis;

    public function __construct(Skautis $skautis)
    {
        parent::__construct();
        $this->skautis = $skautis;
    }

    protected function startup(): void
    {
        parent::startup();

        $post = $this->request->post;
        if (isset($post['skautIS_Token'])) {
            $this->skautis->init($post);
        }

        $this->template->skautIsAppId = $this->skautis->getConfig()->getAppId();
        $this->wsdl                   = $this->skautis->getWsdlManager()->getSupportedWebServices();
    }

    public function renderDefault(): void
    {
        $info = $this->userService->getInfo();
        if ($this->user->isLoggedIn()) {
            $user              = $this->userService->getUserDetail();
            $info['ID_User']   = $user->ID;
            $info['ID_Person'] = $user->ID_Person;
        }

        $this->template->setParameters(
            [
                'info' => Debugger::dump($info, true),
                'request' => Debugger::dump($this->session->getSection(self::SESSION_NAMESPACE)->request, true),
                'response' => Debugger::dump($this->session->getSection(self::SESSION_NAMESPACE)->response, true),
            ]
        );
    }

    public function createComponentTestForm(string $name): Form
    {
        $form = new Form($this, $name);
        $form->getElementPrototype()->class('aja');
        $form->addSelect('wsdl', 'WSDL', $this->wsdl)
            ->addRule(Form::FILLED, 'Musís vybrat WSDL')
            ->setDefaultValue('12');
        $form->addText('service', 'Funkce')
            ->setDefaultValue('unitAll')
            ->addRule(Form::FILLED, 'Vypln service');
        $form->addText('cover', 'Obal', 40)
            ->getControlPrototype()
            ->placeholder('Alternativní obal požadavku');
        $form->addTextArea('args', 'Parametry', 40, 13)
            ->setDefaultValue('ID_UnitParent : 24404')
            ->getControlPrototype()->setClass('input-xlarge');

        $form->addSubmit('send', 'Odeslat')
            ->getControlPrototype()->setClass('btn btn-primary');
        $form->onSuccess[] = [$this, $name . 'Submitted'];

        $sess = $this->getSession(self::SESSION_NAMESPACE);

        if (isset($sess->defaults) && is_array((array) $sess->defaults)) {
            $form->setDefaults((array) $sess->defaults);
        }

        return $form;
    }

    public function testFormSubmitted(Form $form): void
    {
        $sess           = $this->getSession(self::SESSION_NAMESPACE);
        $values         = $form->getValues();
        $sess->defaults = $values;

        $args = Neon::decode($values['args']);
        if ($args instanceof Traversable) {
            foreach ($args as $key => $value) {
                if (! ($value instanceof DateTime)) {
                    continue;
                }

                $args[$key] = $value->format('c');
            }
        }

        $cover = trim($values['cover']);
        if ($cover === '') {
            $cover = null;
        }

        $sess->request = $this->prepareArgs([$args, $cover], $values['service']);
        try {
            $ret = $this->skautis->{$this->wsdl[$values['wsdl']]}->{$values['service']}($args, $cover);
        } catch (Throwable $e) {
            $this->flashMessage($e->getMessage(), 'fail');
            $sess->response = $e->getMessage();
            $this->redirect('this');
        }

        $sess->response = $ret;

        if (! $this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('flash');
            $this->redrawControl('form');
            $this->redrawControl('testResponse');
        }
    }

    /**
     * @param mixed $arguments
     * @param mixed $functionName
     *
     * @return mixed[]
     */
    protected function prepareArgs($arguments, $functionName): array
    {
        if (! isset($arguments[0]) || ! is_array($arguments[0])) {
            $arguments[0] = [];
        }

        if (! array_key_exists('ID_Application', $arguments[0])) {
            $arguments[0]['ID_Application'] = $this->skautis->getConfig()->getAppId();
        }

        $args = $arguments[0];

        if (isset($arguments[1]) && $arguments[1] !== null) {//pokud je zadan druhy parametr tak lze prejmenovat obal dat
            $matches   = array_reverse(preg_split('~/~', $arguments[1])); //rozdeli to na stringy podle /
            $matches[] = 0; //zakladni obal 0=>...
            foreach ($matches as $value) {
                $args = [$value => $args];
            }
        } else {
            $functionName = strtolower(substr($functionName, 0, 1)) . substr($functionName, 1); //nahrazuje lcfirst
            $args         = [[$functionName . 'Input' => $args]];
        }

        return $args;
    }
}
