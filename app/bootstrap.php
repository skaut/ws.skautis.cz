<?php

require LIBS_DIR . '/Nette/loader.php';

// Configure application
$configurator = new Configurator;

$configurator->setTempDirectory(dirname(__FILE__) . '/temp');
$configurator->setDebugMode(array("89.177.96.123"));
$configurator->enableDebugger(dirname(__FILE__) . '/log', "sinacek@gmail.com");


//Debugger::$strictMode = TRUE;
//Debugger::$maxDepth = 6;

$configurator->addConfig(dirname(__FILE__) . '/config.neon');

$configurator->createRobotLoader()
    ->addDirectory(APP_DIR)
    ->addDirectory(LIBS_DIR)
    ->register();

$container = $configurator->createContainer();

// Setup router
$router = new RouteList;
$router[] = new Route('index.php', ':Default:default', Route::ONE_WAY);
$router[] = new Route('sign/<action>[/back-<backlink>]', array(
    "presenter" => "Auth",
    "action" => "default",
    "backlink" => NULL
));

$router[] = new Route('<presenter>[/<action>]', array(
            "presenter" => array(
                Route::VALUE => 'default',
                Route::FILTER_TABLE => array(
                    'zadost' => 'AppRequest',
                    'testovani' => 'Test',
                ),
            ),
            "action" => "default",
        ));


$router[] = new SimpleRouter('Default:default');

$container->router = $router;


// Configure and run the application!
$application = $container->application;
//$application->catchExceptions = $configurator->isProductionMode();
$application->catchExceptions = TRUE;
//$application->catchExceptions = FALSE;
//$application->errorPresenter = 'Error';
 if (!Environment::isConsole())
   $application->run();
