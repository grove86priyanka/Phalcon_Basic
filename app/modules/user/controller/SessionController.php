<?php

/**
 * Session Controller for User Section
 * @author Amit
 */

namespace App\Modules\User\Controller;

use App\Model\User;
use App\Modules\User\Form\LoginForm;
use App\Modules\User\Form\RegisterForm;
use App\Modules\User\Form\RequestSetupForm;
use App\Modules\User\Form\PasswordResetForm;
use App\Library\Mail;

class SessionController extends CBaseController
{

    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->response->redirect('/user/login');
    }

    /**
     * Log in Action
     */
    public function loginAction() {

        $redirect = '/user';
        $controllerName = $this->router->getControllerName();
        /**
         * Prevent user intentionaly access
         * login action while logged in
         */
        if ($controllerName != 'session')
        {
            $redirect = $this->router->getRewriteUri();
        }
        if ($this->user->isOnline())
        {
            return $this->response->redirect($redirect);
        }
        $loginForm = new LoginForm();
        if ($this->request->isPost())
        {
            if ($loginForm->isValid($this->request->getPost()))
            {
                $userUsername = $this->request->getPost('username');
                $userPassword = $this->request->getPost('password');
                $userModel = User::findFirst(array(
                            'conditions' => 'email = :username:',
                            'bind' => array('username' => $userUsername)
                ));

                if ($userModel && $userModel->checkPasswordHash($userPassword, true))
                {
                    if ($userModel->isActive())
                    {
                        $this->user->setData($userModel);
                        $this->user->login();
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
        $this->tag->setTitle('User Login');
        $this->view->disableHeaderMessage = TRUE;
        $this->view->form = $loginForm;
    }

    /**
     * Register Action
     */
    public function registerAction() {

        if($this->user->isOnline()) {
            return $this->response->redirect('/');
        }
        $this->tag->setTitle('User Register');
        $registerForm = new RegisterForm();

        if ($this->request->isPost() && $registerForm->isValid($this->request->getPost())) {
            
            $strUserEmail = $this->request->getPost('email', 'email');
            $strPassword = $this->request->getPost('password');
            $strFirstName = $this->request->getPost('first_name', array('string', 'striptags'));
            $strLastName = $this->request->getPost('last_name', array('string', 'striptags'));
            
            $user = new User();
            $user->setEmail($strUserEmail);
            $user->setFirst_name($strFirstName);
            $user->setLast_name($strLastName);
            $user->setStatus(User::ACTIVE_STATUS_CODE);
            $user->setDate_created(date('Y-m-d H:i:s', time()));
            
            if ($user->save()) {
                $user = User::findFirst($user->getId());    // need to re-get the object because of phalcons bug where they default all non-set variables to null (which blew up because email_verification_status and payment_verified is NOT NULL)
                $user->hashPassword($strPassword);
                $user->save();

                $this->_emailVerificationMail($user);
                $this->user->setData($user);
                $this->user->login();
                $this->response->redirect('/');
            }
        }

        $this->view->form = $registerForm;
    }

    /**
     * Email verification Mail Action
     */
    private function _emailVerificationMail($userObj) {
        

        $mail = new Mail();
        $mail->setTo($userObj->email, $userObj->getFullName());
        $mail->setSubject('New Account created Successfully');
        $mailBodyFinal = "Hey ".$userObj->getFullName()."
            Congrets, Your Account created successfully.
        ";
        $mail->setBody($mailBodyFinal); 
        return ($mail->send()) ? TRUE : FALSE;
    }

        /**
     * Reset Password Action
     */
    public function requestsetupAction() {

        $this->tag->setTitle('User Forgot Password');
        $requestSetupForm = new RequestSetupForm();
        // check data is posted or not
        if ($this->request->isPost()) {
            if ($requestSetupForm->isValid($this->request->getPost())) {
                $userEmail = $this->request->getPost('email', 'email');
                $existingUser = User::findFirstByEmail($userEmail);
                if ($existingUser) {
                   
                    $mail = new Mail();
                    $mail->setTo($existingUser->email, $existingUser->getFullName());
                    $mail->setSubject('Reset Password');
                    $mailBodyFinal = "Hey ".$existingUser->getFullName()."
                        Please click below link to reset your password.

                    " . $existingUser->generateResetLink();
                    $mail->setBody($mailBodyFinal); 
                    if($mail->send()) $this->flash->success("Please Check Your Email To Reset Your Password!!");
                    
                } else {
                    $this->flash->error("This email address is not registered. Please check again.");
                }
            } else {
                foreach ($requestSetupForm->getMessages() as $message) {
                    $this->flash->error((string) $message);
                }
            }
        }       
        $this->view->form = $requestSetupForm; 
    }

    /**
     * Reset Password Action
     * @param string $param
     */
    public function resetAction($param = '') {
        $status = FALSE;
        $existingUser = $userEmail = NULL;
        if ($param) {
            $decrypted = $this->crypt->decryptBase64URL($param);
            if ($unserializeCred = @unserialize($decrypted)) {
                $userId = $unserializeCred['user_id'];
                $userEmail = $unserializeCred['email'];
                $requestValidUpto = $unserializeCred['valid_upto'];
                if ($userId && $userEmail) {
                    $existingUser = User::findFirst(array(
                                'conditions' => 'user_id = :id: AND email = :email:',
                                'bind' => array('id' => $userId, 'email' => $userEmail)
                    ));
                    if ($existingUser) {
                        if ($requestValidUpto && $requestValidUpto >= time()) {
                            $status = TRUE;
                        }
                    }
                }
            }
        }
        if ($status) {
            $this->tag->setTitle('Reset Password');
            $passwordResetForm = new PasswordResetForm();
            if ($this->request->isPost()) {
                if ($passwordResetForm->isValid($this->request->getPost())) {
                    $existingUser->hashPassword($this->request->getPost('password'));
                    if ($existingUser->update()) {
                        $this->flash->success("Password has been reset successfully!!");
                        $this->response->redirect('/');
                    }
                }
            }
            $this->view->strUserEmail = $userEmail;
            $this->view->form = $passwordResetForm;
        } else {
            $this->flash->error("Token Expired, reset again.");
            return $this->response->redirect('/');
            return FALSE;
        }
    }

    /**
     * Log out Action
     */
    public function logoutAction() {

        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        /**
         * Prevent User intentionaly access
         * logout action while guest
         */
        if (!$this->user->isOnline())
        {
            $this->response->redirect('/user/login');
            return false;
        }
        $this->user->logout();
    }

}
