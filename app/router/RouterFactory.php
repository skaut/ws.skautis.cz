<?php

namespace App;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\SimpleRouter;

class RouterFactory
{
    /**
     * @return IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();
        $router->addRoute('index.php', 'Default:default', Route::ONE_WAY);
        $router->addRoute(
            'sign/<action>[/back-<backlink>]', [
            "presenter" => "Auth",
            "action" => "default",
            "backlink" => null
            ]
        );

        $router->addRoute(
            'ws', [
            "presenter" => "Default",
            "action" => "ws",
            ], Route::ONE_WAY
        );

        $router->addRoute(
            '<presenter>[/<action>]', [
            "presenter" => [
                Route::VALUE => 'Default',
                Route::FILTER_TABLE => [
                    'zadost' => 'AppRequest',
                    'testovani' => 'Test',
                ],
            ],
            "action" => "default",
            ]
        );
        //$router[] = new SimpleRouter('Default:default');
        return $router;
    }

}
