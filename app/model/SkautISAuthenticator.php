<?php

namespace Skautis;

use Nette;

/**
 * používat pouze pro data ze skautISu, nikdy nenechávat aby uživatel zadal sám svoje ID!
 */
class Authenticator implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    public function authenticate(array $credentials)
    {
        return new Nette\Security\Identity($credentials[0]->ID_User);
    }

}
