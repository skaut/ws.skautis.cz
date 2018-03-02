<?php

class WsService extends BaseService
{
    public function createNewApplication($name, $desc, $url, $urlLogin, $urlLogout)
    {
        return $this->skautis->app->RemoteApplicationInsert([
            "DisplayName" => $name,
            "Description" => $desc,
            "Enabled" => TRUE,
            "IP" => "",
            "Url" => $url,
            "UrlLoginPage" => $urlLogin,
            "UrlLogoutPage" => $urlLogout,
            "IsAnonymous" => TRUE
        ]);

    }
}
