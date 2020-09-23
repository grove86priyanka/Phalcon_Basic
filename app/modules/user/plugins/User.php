<?php

/**
 * Admin Plugins Class
 * @author Amit
 */

namespace App\Modules\User\Plugins;

use Phalcon\Mvc\User\Plugin;

class User extends Plugin
{

    private $userData = null;
    public $sessionKey = 'user';

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->userData = $this->session->get($this->sessionKey);
    }

    /**
     * Check Admin Session
     * @return boolean
     */
    public function isOnline()
    {
        if ($this->getData())
        {
            if ($this->getData()->getId() > 0)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Getting Admin Data
     * @return object
     */
    public function getData()
    {
        return $this->userData;
    }

    /**
     * Set Admin Model Resource
     * @param \App\Model\Admin $userData
     */
    public function setData(\App\Model\User $userData)
    {
        $this->userData = $userData;
    }

    /**
     * Set Admin Model Resource to Session
     */
    public function login()
    {
        $this->session->set($this->sessionKey, $this->userData);
    }

    /**
     * Remove Admin Model Resource From Session
     */
    public function logout()
    {
        $this->session->remove($this->sessionKey);
        $this->response->redirect('/');
    }

    /**
     * Admin Access Permission
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    public function isAllowed($controller, $action = 'index')
    {
        $controllerName = strtolower(trim($controller));
        $actionName = strtolower(trim($action));
        if ($this->isOnline())
        {
            return true;
        }
        return false;
    }

}
