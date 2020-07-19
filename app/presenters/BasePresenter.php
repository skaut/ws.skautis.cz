<?php

declare(strict_types=1);

namespace App;

use Exception;
use Model\UserService;
use Nette;
use Throwable;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    protected ?string $backlink;

    protected UserService $userService;

    public function injectUserService(UserService $us): void
    {
        $this->userService = $us;
    }

    protected function startup(): void
    {
        parent::startup();
        $this->template->backlink = $this->getParameter('backlink');
    }

    public function handleChangeRole(?int $roleId= null): void
    {
        $this->userService->updateSkautISRole($roleId);
        $this->redirect('this');
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        try {
            if (! $this->user->isLoggedIn() || ! $this->userService->isLoggedIn(true)) {
                throw new Exception('Uživatel by odhlášen');
            }

            $this->template->myRoles = $this->userService->getAllSkautISRoles();
            $this->template->myRole  = $this->userService->getRoleId();
        } catch (Throwable $ex) {
            $this->user->logout();
        }
    }
}
