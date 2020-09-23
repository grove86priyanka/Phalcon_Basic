<?php

/*
 * Admin Modules Manager
 * @author Amit
 */

namespace App\Modules\Admin;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\ModuleDefinitionInterface;
use App\Modules\Admin\Plugins\Session as SessionSecurity;
use App\Modules\Admin\Plugins\Admin as AdminComponent;
use App\Modules\Admin\Plugins\Nav as AdminNav;
use App\Library\Breadcrumbs\Breadcrumbs;

class Module implements ModuleDefinitionInterface
{

    /**
     * Register a specific autoloader for the section
     */
    public function registerAutoloaders(\Phalcon\DiInterface $di = NULL)
    {
        $loader = new Loader();
        $loader->registerNamespaces(array(
            'App\Modules\Admin\Controller' => ADMIN_MODULES_PATH . 'controller' . DS,
            'App\Modules\Admin\Form' => ADMIN_MODULES_PATH . 'form' . DS,
            'App\Modules\Admin\Plugins' => ADMIN_MODULES_PATH . 'plugins' . DS,
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
            $eventsManager->attach('dispatch:beforeDispatchLoop', new SessionSecurity());
            $dispatcher = new Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace("App\Modules\Admin\Controller");
            return $dispatcher;
        });

        // Registering the view component
        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(ADMIN_MODULES_PATH . 'view');
            $view->setLayoutsDir($this->get('config')->view->layoutsDir);
            $view->setPartialsDir($this->get('config')->view->partialsDir);
            $view->setMainView($this->get('config')->view->mainView);
            $view->setTemplateAfter('admin');
            $view->moduleName = 'admin';    // make moduleName availible to all views

            $eventsManager = new EventsManager();
            $view->setEventsManager($eventsManager);

            return $view;
        });

        // Register a Admin Component
        $di->set('admin', function() {
            return new AdminComponent();
        });
        // Register a nav element component
        $di->set('nav', function() {
            return new AdminNav();
        });

        // Register a BreadCumbs Component
        $di->set('breadcrumbs', function() {
            $breadcrumbs = new Breadcrumbs();
            $breadcrumbs->setTemplate(
                '<li class="breadcrumb-item"><a href="{{link}}">{{label}}</a></li>', // linked
                '<li class="breadcrumb-item active">{{label}}</li>',// not linked
                ''// first icon
            );
            $breadcrumbs->add('Home', $this->get('url')->get('/admin/'));
            return $breadcrumbs;
        });
    }

}
