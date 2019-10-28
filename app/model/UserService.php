<?php

/**
 * @author Hána František
 */
class UserService extends BaseService
{

    /**
     * varcí ID role aktuálně přihlášeného uživatele
     * @return int|null
     */
    public function getRoleId()
    {
        return $this->skautis->getUser()->getRoleId();
    }

    /**
     * vrací pole
     * @return array všech dostupných rolí přihlášeného uživatele
     */
    public function getAllSkautISRoles($activeOnly = TRUE)
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
     * @param ID_Role $id
     */
    public function updateSkautISRole($id)
    {
        $response = $this->skautis->user->LoginUpdate(["ID_UserRole" => $id, "ID" => $this->skautis->getUser()->getLoginId()]);
        if ($response) {
            $this->skautis->getUser()->updateLoginData(NULL, $id, $response->ID_Unit);
        }
    }

    /**
     * vrací kompletní seznam informací o přihlášené osobě
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
     * @return bool
     */
    public function isLoggedIn($hardCheck = FALSE)
    {
        return $this->skautis->getUser()->isLoggedIn($hardCheck);
    }

    public function resetLoginData()
    {
        $this->skautis->getUser()->resetLoginData();
    }

    /**
     *
     * @param type $table - např. ID_EventGeneral, NULL = oveření nad celou tabulkou
     * @param type $id - id ověřované akce - např EV_EventGeneral_UPDATE
     * @param type $ID_Action - tabulka v DB skautisu
     * @return BOOL|stdClass|array
     */
    public function actionVerify($table, $id = NULL, $ID_Action = NULL)
    {

        $res = $this->skautis->user->ActionVerify([
            "ID" => $id,
            "ID_Table" => $table,
            "ID_Action" => $ID_Action,
        ]);
        if ($ID_Action !== NULL) { //pokud je zadána konrétní funkce pro ověřování, tak se vrací BOOL
            if ($res instanceof stdClass) {
                return FALSE;
            }
            if (is_array($res)) {
                return TRUE;
            }
        }
        if (is_array($res)) {
            $tmp = [];
            foreach ($res as $v) {
                $tmp[$v->ID] = $v;
            }
            return $tmp;
        }
        return $res;
    }

}
