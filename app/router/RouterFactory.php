<?php

namespace App;

use Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route,
    Nette\Application\Routers\SimpleRouter;

/**
 * Router factory.
 */
class RouterFactory {

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter() {
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
