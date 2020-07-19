<?php

declare(strict_types=1);

namespace Model;

use Skautis\Skautis;

class AuthService
{
    protected Skautis $skautis;

    public function __construct(Skautis $skautIS)
    {
        $this->skautis = $skautIS;
    }

    /**
     * vrací přihlašovací url
     */
    public function getLoginUrl(?string $backlink): string
    {
        return $this->skautis->getLoginUrl($backlink);
    }

    /**
     * nastavuje základní udaje po prihlášení do SkautISu
     *
     * @param string[] $arr
     */
    public function setInit(array $arr): void
    {
        $this->skautis->setLoginData($arr);
    }

    /**
     * vrací url pro odhlášení
     */
    public function getLogoutUrl(): string
    {
        return $this->skautis->getLogoutUrl();
    }
}
