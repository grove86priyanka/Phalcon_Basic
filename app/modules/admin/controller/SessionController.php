<?php

/**
 * Session Controller for Admin Section
 * @author Amit
 */

namespace App\Modules\Admin\Controller;

use App\Model\Admin;
use App\Modules\Admin\Form\LoginForm;

class SessionController extends CBaseController
{

    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->response->redirect('/admin/login');
    }

    /**
     * Log in Action
     */
    public function loginAction()
    {
        /**
         * Prevent admin intentionaly access
         * login action while logged in
         */
        $redirect = '/admin';
        $controllerName = $this->router->getControllerName();
        if ($controllerName != 'session')
        {
            $redirect = $this->router->getRewriteUri();
        }
        if ($this->admin->isOnline())
        {
            return $this->response->redirect($redirect);
        }
        $loginForm = new LoginForm();
        if ($this->request->isPost())
        {
            if ($loginForm->isValid($this->request->getPost()))
            {
                $adminUsername = $this->request->getPost('username');
                $adminPassword = $this->request->getPost('password');
                $adminModel = Admin::findFirst(array(
                            'conditions' => 'username = :username: AND status IN ({status:array})',
                            'bind' => array('username' => $adminUsername, 'status' => [Admin::ACTIVE_STATUS_CODE, Admin::INACTIVE_STATUS_CODE])
                ));

                if ($adminModel && $adminModel->checkPasswordHash($adminPassword, true))
                {
                    if ($adminModel->isActive())
                    {
                        $this->admin->setData($adminModel);
                        $this->admin->login();
                        return $this->response->redirect($redirect);
                    } else
                    {
                        $this->flash->error('Account is Inactive. Contact Administrator!!');
                    }
                } else
                {
                    $this->flash->error("Invalid User name/Password!!");
                }
            }
        }
        $this->initializeLayout('admin');
        $this->tag->setTitle('Administrator Login');
        $this->view->disableHeaderMessage = TRUE;
        $this->view->form = $loginForm;
    }

    /**
     * Log out Action
     */
    public function logoutAction()
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        /**
         * Prevent admin intentionaly access
         * logout action while guest
         */
        if (!$this->admin->isOnline())
        {
            $this->response->redirect('/admin/login');
            return false;
        }
        $this->admin->logout();
    }

}
