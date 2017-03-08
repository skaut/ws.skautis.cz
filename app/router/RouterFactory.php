<?php

namespace App;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\SimpleRouter;

/**
 * Router factory.
 */
class RouterFactory
{

    /**
     * RouterFactory constructor.
     * @param bool $ssl
     */
    public function __construct($ssl)
    {
        // Disable https for development
        Route::$defaultFlags = $ssl ? Route::SECURED : 0;
    }

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();
        $router[] = new Route('index.php', 'Default:default', Route::ONE_WAY);
        $router[] = new Route('sign/<action>[/back-<backlink>]', array(
            "presenter" => "Auth",
            "action" => "default",
            "backlink" => NULL
        ));

        $router[] = new Route('ws', array(
            "presenter" => "Default",
            "action" => "ws",
        ), Route::ONE_WAY);

        $router[] = new Route('<presenter>[/<action>]', array(
            "presenter" => array(
                Route::VALUE => 'Default',
                Route::FILTER_TABLE => array(
                    'zadost' => 'AppRequest',
                    'testovani' => 'Test',
                ),
            ),
            "action" => "default",
        ));
        $router[] = new SimpleRouter('Default:default');
        return $router;
    }

}
