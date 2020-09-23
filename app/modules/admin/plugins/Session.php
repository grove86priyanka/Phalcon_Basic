<?php

/**
 * Session Security Plugins
 * @author Amit
 */

namespace App\Modules\Admin\Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

class Session extends Plugin
{

    public function beforeDispatchLoop(Event $event, Dispatcher $dispatcher)
    {
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        if (!$this->admin->isOnline())
        {
            if ($controller != 'session')
            {
                return $dispatcher->forward(array(
                            'module' => 'admin',
                            'controller' => 'session',
                            'action' => 'login',
                ));
            }
        } else
        {
            if (!$this->admin->isAllowed($controller, $action))
            {
                $this->flash->error("Access not allowed");
                $this->response->redirect('/admin');
            }
        }
    }

}
