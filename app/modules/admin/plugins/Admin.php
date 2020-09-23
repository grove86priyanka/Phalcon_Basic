<?php

/**
 * Admin Plugins Class
 * @author Amit
 */

namespace App\Modules\Admin\Plugins;

use Phalcon\Mvc\User\Plugin;

class Admin extends Plugin
{

    private $adminData = null;
    public $sessionKey = 'admin';

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->adminData = $this->session->get($this->sessionKey);
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
        return $this->adminData;
    }

    /**
     * Set Admin Model Resource
     * @param \App\Model\Admin $adminData
     */
    public function setData(\App\Model\Admin $adminData)
    {
        $this->adminData = $adminData;
    }

    /**
     * Set Admin Model Resource to Session
     */
    public function login()
    {
        $this->session->set($this->sessionKey, $this->adminData);
    }

    /**
     * Remove Admin Model Resource From Session
     */
    public function logout()
    {
        $this->session->remove($this->sessionKey);
        $this->response->redirect('/admin');
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
