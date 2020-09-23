<?php

/**
 * Session Security Plugins
 * @author Amit
 */

namespace App\Modules\User\Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

class Session extends Plugin
{

    public function beforeDispatch(Event $event, Dispatcher $dispatcher)
    {
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        if (!$this->user->isOnline())
        {
            if ($controller != 'session')
            {
                return $dispatcher->forward(array(
                            'module' => 'user',
                            'controller' => 'session',
                            'action' => 'index',
                ));
            }
        }
    }

}
