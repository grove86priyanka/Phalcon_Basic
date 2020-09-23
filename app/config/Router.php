<?php

use Phalcon\Mvc\Router;

$moduleConfigurations = [];

if (!isset($router))
    $router = new Router();

$router->setDefaultModule("front");
$router->setDefaultController('index');
$router->setDefaultAction('index');
$i = 0;
foreach (['front', 'user', 'admin'] as $module)
{
    $modulesUrl = '/';
    if ($module != 'front')
    {
        $modulesUrl .= $module;
    }

    $router->add($modulesUrl, [
        'module' => $module,
        'controller' => "index",
        'action' => "index"
    ]);

    if ($module != 'front')
    {
        if ($i > 0)
        {
            $modulesUrl .= '/';
            $router->add($modulesUrl, [
                'module' => $module,
                'controller' => "index",
                'action' => "index"
            ]);
        }
    }

    $router->add($modulesUrl . ':controller(|\/)', [
        'module' => $module,
        'controller' => 1,
        'action' => "index"
    ]);

    $router->add($modulesUrl . ':controller/:action(|\/)', [
        'module' => $module,
        'controller' => 1,
        'action' => 2
    ]);

    $router->add($modulesUrl . ':controller/:action/:params', [
        'module' => $module,
        'controller' => 1,
        'action' => 2,
        'params' => 3
    ]);

    $i++;

    $moduleConfigurations[$module] = [
        'className' => 'App\Modules\\' . ucfirst($module) . '\Module',
        'path' => APPPATH . 'modules' . DS . $module . DS . 'Module.php',
    ];
}

//User Login/Register/Requestsetup/Logout
$router->add('/user/{action:(login|logout|register|requestsetup)}(|\/)', [
    'module' => 'user',
    'controller' => 'session',
    'action' => 1
]);

//Admin Login/Logout
$router->add('/admin/{action:(login|logout)}(|\/)', [
    'module' => 'admin',
    'controller' => 'session',
    'action' => 1
]);

//Static Pages
$router->add('#^/(contact|privacy-policy|terms-conditions)(|\/)$#ui', [
    'module' => 'front',
    'controller' => 'page',
    'action' => 'show',
    "params" => 0,
]);

$router->handle();
