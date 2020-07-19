<?php

declare(strict_types=1);

namespace Skautis;

use Nette;

/**
 * používat pouze pro data ze skautISu, nikdy nenechávat aby uživatel zadal sám svoje ID!
 */
class SkautISAuthenticator implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    /**
     * @param mixed[] $credentials
     */
    public function authenticate(array $credentials): Nette\Security\IIdentity
    {
        return new Nette\Security\Identity($credentials[0]->ID_User);
    }
}
