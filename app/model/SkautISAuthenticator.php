<?php

namespace Sinacek;

use Nette;
use Nette\Security\IIdentity;

/**
 * používat pouze pro data ze skautISu, nikdy nenechávat aby uživatel zadal sám svoje ID!
 * @author Hána František <sinacek@gmail.com>
 */
class SkautisAuthenticator implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    public function authenticate(array $credentials): IIdentity
    {
        $data = $credentials[0];
        return new Nette\Security\Identity($data->ID_User);
    }

}
