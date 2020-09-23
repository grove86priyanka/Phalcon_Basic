<?php

/*
 * User Modules Manager
 * @author Amit
 */

namespace App\Modules\User;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\ModuleDefinitionInterface;
use App\Modules\User\Plugins\Session as SessionSecurity;
use App\Modules\User\Plugins\User as UserComponent;

class Module implements ModuleDefinitionInterface
{

    /**
     * Register a specific autoloader for the section
     */
    public function registerAutoloaders(\Phalcon\DiInterface $di = NULL)
    {
        $loader = new Loader();
        $loader->registerNamespaces(array(
            'App\Modules\User\Controller' => USER_MODULES_PATH . 'controller' . DS,
            'App\Modules\User\Form' => USER_MODULES_PATH . 'form' . DS,
            'App\Modules\User\Plugins' => USER_MODULES_PATH . 'plugins' . DS,
        ));
        $loader->register();
    }

    /**
     * Register specific services for the module
     */
    public function registerServices(\Phalcon\DiInterface $di)
    {

        // Registering a dispatcher
        $di->setShared('dispatcher', function() use ($di) {
            $eventsManager = $di->getShared('eventsManager') ?: new EventsManager();
            $eventsManager->attach('dispatch:beforeDispatch', new SessionSecurity());
            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace("App\Modules\User\Controller");
            return $dispatcher;
        });
        // Registering the view component
        $di->setShared('view', function() {
            $view = new View();
            $view->setViewsDir(USER_MODULES_PATH . 'view');
            $view->setLayoutsDir($this->get('config')->view->layoutsDir);
            $view->setPartialsDir($this->get('config')->view->partialsDir);
            $view->setMainView($this->get('config')->view->mainView);
            $view->setTemplateAfter('users');
            $view->moduleName = 'user';    // make moduleName availible to all views

            return $view;
        });

        // Register a User Component
        $di->set('user', function() {
            return new UserComponent();
        });
    }

}
