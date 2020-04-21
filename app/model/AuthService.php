<?php

class AuthService extends \BaseService
{

    /**
     * vrací přihlašovací url
     *
     * @param  string $backlink
     * @return string
     */
    public function getLoginUrl($backlink)
    {
        return $this->skautis->getLoginUrl($backlink);
    }

    /**
     * nastavuje základní udaje po prihlášení do SkautISu
     *
     * @param array $arr
     */
    public function setInit(array $arr)
    {
        $this->skautis->setLoginData($arr);
    }

    /**
     * vrací url pro odhlášení
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->skautis->getLogoutUrl();
    }

}
