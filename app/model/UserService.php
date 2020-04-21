<?php

class UserService extends BaseService
{

    /**
     * varcí ID role aktuálně přihlášeného uživatele
     *
     * @return type
     */
    public function getRoleId()
    {
        return $this->skautis->getUser()->getRoleId();
    }

    /**
     * vrací pole
     *
     * @return array všech dostupných rolí přihlášeného uživatele
     */
    public function getAllSkautISRoles($activeOnly = true)
    {
        return $this->skautis->user->UserRoleAll(["ID_User" => $this->getUserDetail()->ID, "IsActive" => $activeOnly]);
    }

    public function getUserDetail()
    {
        $id = __FUNCTION__;
        if (!($res = $this->load($id))) {//cache v rámci pozadavku
            $res = $this->save($id, $this->skautis->user->UserDetail());
        }
        return $res;
    }

    /**
     * změní přihlášenou roli do skautISu
     *
     * @param ID_Role $id
     */
    public function updateSkautISRole($id)
    {
        $response = $this->skautis->user->LoginUpdate(["ID_UserRole" => $id, "ID" => $this->skautis->getUser()->getLoginId()]);
        if ($response) {
            $this->skautis->getUser()->updateLoginData(null, $id, $response->ID_Unit);
        }
    }

    /**
     * vrací kompletní seznam informací o přihlášené osobě
     *
     * @return type
     */
    public function getPersonalDetail()
    {
        $user = $this->getUserDetail();
        $person = $this->skautis->org->personDetail((["ID" => $user->ID_Person]));
        return $person;
    }

    /**
     * kontroluje jestli je přihlášení platné
     *
     * @return type
     */
    public function isLoggedIn($hardCheck = false)
    {
        return $this->skautis->getUser()->isLoggedIn($hardCheck);
    }

    public function resetLoginData()
    {
        $this->skautis->getUser()->resetLoginData();
    }

}
