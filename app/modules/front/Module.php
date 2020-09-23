<?php

/*
 * Front Module Manager
 * @author Amit
 */

namespace App\Modules\Front;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\ModuleDefinitionInterface;
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
            'App\Modules\Front\Controller' => FRONT_MODULES_PATH . 'controller' . DS,
            'App\Modules\Front\Plugins' => FRONT_MODULES_PATH . 'plugins' . DS,
            'App\Modules\Front\Form' => FRONT_MODULES_PATH . 'form' . DS,
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
            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace("App\Modules\Front\Controller");

            return $dispatcher;
        });

        // Registering the view component
        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(FRONT_MODULES_PATH . 'view');
            $view->setLayoutsDir($this->get('config')->view->layoutsDir);
            $view->setPartialsDir($this->get('config')->view->partialsDir);
            $view->setMainView($this->get('config')->view->mainView);
            $view->setTemplateAfter('main');
            $view->moduleName = 'front';    // make moduleName availible to all views

            return $view;
        });

        // Register a User Component
        $di->set('user', function() {
            return new UserComponent();
        });
    }

}
