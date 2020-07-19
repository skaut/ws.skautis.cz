<?php

declare(strict_types=1);

namespace Model;

use Skautis\Skautis;
use stdClass;

class UserService
{
    protected Skautis $skautis;

    public function __construct(Skautis $skautIS)
    {
        $this->skautis = $skautIS;
    }

    /**
     * @return mixed[]
     */
    public function getInfo(): array
    {
        return [
            "ID_Login" => $this->skautis->getUser()->getLoginId(),
            "ID_Role" => $this->skautis->getUser()->getRoleId(),
            "ID_Unit" => $this->skautis->getUser()->getUnitId(),
        ];
    }


    /**
     * varcí ID role aktuálně přihlášeného uživatele
     */
    public function getRoleId(): ?int
    {
        return $this->skautis->getUser()->getRoleId();
    }

    /**
     * vrací pole
     *
     * @return mixed[] všech dostupných rolí přihlášeného uživatele
     */
    public function getAllSkautISRoles(bool $activeOnly = true): array
    {
        return $this->skautis->user->UserRoleAll(['ID_User' => $this->getUserDetail()->ID, 'IsActive' => $activeOnly]);
    }

    public function getUserDetail(): stdClass
    {
        return $this->skautis->user->UserDetail();
    }

    /**
     * změní přihlášenou roli do skautISu
     */
    public function updateSkautISRole(int $id): void
    {
        $response = $this->skautis->user->LoginUpdate(['ID_UserRole' => $id, 'ID' => $this->skautis->getUser()->getLoginId()]);
        if (! $response) {
            return;
        }

        $this->skautis->getUser()->updateLoginData(null, $id, $response->ID_Unit);
    }

    /**
     * vrací kompletní seznam informací o přihlášené osobě
     */
    public function getPersonalDetail(): stdClass
    {
        $user = $this->getUserDetail();

        return $this->skautis->org->personDetail((['ID' => $user->ID_Person]));
    }

    /**
     * kontroluje jestli je přihlášení platné
     */
    public function isLoggedIn(bool $hardCheck = false): bool
    {
        return $this->skautis->getUser()->isLoggedIn($hardCheck);
    }

    public function resetLoginData(): void
    {
        $this->skautis->getUser()->resetLoginData();
    }
}
