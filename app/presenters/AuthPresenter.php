<?php

declare(strict_types=1);

namespace App;

use Model\AuthService;
use Skautis\SkautISAuthenticator;
use Skautis\Wsdl\AuthenticationException;

class AuthPresenter extends BasePresenter
{
    protected AuthService $authService;

    public function __construct(AuthService $as)
    {
        parent::__construct();
        $this->authService = $as;
    }

    /**
     * pokud je uziatel uz prihlasen, staci udelat referesh
     *
     * @param bool $final - je to konečné přesměrování? použít při problémem se zacyklením
     */
    public function actionDefault(string $backlink): void
    {
        if ($this->user->isLoggedIn()) {
            if ($backlink) {
                $this->restoreRequest($backlink);
            }
        }

        $this->redirect(':Default:');
    }

    /**
     * přesměruje na stránku s přihlášením
     */
    public function actionLogOnSkautIs(?string $backlink = null): void
    {
        $this->redirectUrl($this->authService->getLoginUrl($backlink));
    }

    /**
     * zajistuje spracovani prihlaseni na skautIS
     */
    public function actionSkautIS(?string $ReturnUrl = null): void
    {
        $post = $this->request->post;
        if (! isset($post['skautIS_Token'])) { //pokud není nastavený token, tak zde nemá co dělat
            $this->redirect(':Default:');
        }

        //        Debugger::log("AuthP: ".$post['skautIS_Token']." / ". $post['skautIS_IDRole'] . " / " . $post['skautIS_IDUnit'], "auth");
        try {
            $this->authService->setInit($post);

            if (! $this->userService->isLoggedIn()) {
                throw new AuthenticationException('Nemáte platné přihlášení do skautISu');
            }

            $me = $this->userService->getPersonalDetail();

            $this->user->setExpiration('+ 29 minutes'); // nastavíme expiraci
            $this->user->setAuthenticator(new SkautISAuthenticator());
            $this->user->login($me);

            if (isset($ReturnUrl)) {
                $this->restoreRequest($ReturnUrl);
            }
        } catch (AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
            $this->redirect(':Auth:');
        }

        $this->presenter->redirect(':Default:');
    }

    /**
     * zajištuje odhlašení ze skautISu
     * SkautIS sem přesměruje po svém odhlášení
     */
    public function actionLogoutSIS(): void
    {
        $this->redirectUrl($this->authService->getLogoutUrl());
    }

    public function actionSkautisLogout(): void
    {
        $this->user->logout(true);
        $this->userService->resetLoginData();
        if ($this->request->post['skautIS_Logout']) {
            $this->flashMessage('Byl jsi úspěšně odhlášen ze SkautISu.');
        } else {
            $this->flashMessage('Odhlášení ze skautISu se nezdařilo', 'danger');
        }

        $this->redirect(':Default:');
    }
}
