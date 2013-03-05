<?php
use Nette\Object,
    Nette\Security\IAuthenticator,
    Nette\Security\Identity;

/**
 * používat pouze pro data ze skautISu, nikdy nenechávat aby uživatel zadal sám svoje ID!
 * @author Hána František
 */
class SkautISAuthenticator extends Object implements IAuthenticator {

    public function authenticate(array $credentials) {
        return new Identity($credentials[0]->ID_User);
    }

}
