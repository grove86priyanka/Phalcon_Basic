<?php

/**
 * User Controller
 *
 * @author vishal
 */

namespace App\Modules\Admin\Controller;

use App\Model\User;
use App\Modules\Admin\Form\UserForm;
use App\Library\Pagination\PaginationModel as Pagination;
use App\Helper\Date as DateHelper;

class UserController extends CBaseController
{

    protected $theForm;   // used to prevent double init of the form when forwarding on form validation error
    protected $theUser;   // used to prevent double init of the form when forwarding on form validation error

    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->tag->setTitle('User');
        $this->breadcrumbs->add('User');
        $this->dispatcher->forward(['action' => 'show']);
    }

    /**
     * Displays the users list
     */
    public function showAction() {
        $this->tag->setTitle('Users');
        
        $searchForm = new \App\Modules\Admin\Form\UserSearchForm();

        $params = [];
        if ($where = $this->getFilters()) $params = $where;
        // Sorting
        $sortLib = $this->buildSorting($this->url->getBackendUri('', '', 'user'), 'user_id');
        $sortLib->setMapping([
                                'user_id' => ['App\Model\User.user_id'],
                                'user_name' => ['App\Model\User.first_name','App\Model\User.last_name'],
                                'user_email' => ['App\Model\User.email'],
                                'user_status'      => ['App\Model\User.status'],
                            ]);
        if (!($params['order'] = $sortLib->getSort('App\Model\User'))) unset($params['order']);

        $users = User::getUsers($params + ['returnBuilder' => true]);
        $limit = ($this->request->has('per_page_item') ? $this->request->get('per_page_item') : $searchForm->getDefaultPerPageVal());
        
        $page = $this->request->get('page', 'int', 1);
        $pager = new Pagination([
            'builder' => $users,
            'limit' => $limit,
            "page" => $page,
        ]);

        $pager->setIgnoreParams(['page']);
        $pager->setUriPattern($this->url->getBackendUri() . '?page={page}');
        $this->view->pager = $pager;
        $this->view->users = $pager->getItems();
        $this->view->searchForm = $searchForm;
        $this->view->sort = $sortLib;
    }

    protected function getFilters()
    {
        $where = [];
        if ($this->request->get('keywords') || $this->dispatcher->hasParam('keywords')) {
            $where['keywords'] = $this->request->get('keywords');
        }

        if ($this->request->get('status') || $this->dispatcher->hasParam('status')) {
            $where['status'] = $this->request->get('status');
        }
                
        return $where;
    }

     /**
     * Displays the add user form
     */
    public function newAction() {
        
        $this->tag->setTitle('New User');
        $this->breadcrumbs->add('User',$this->url->getBackend());
        $this->breadcrumbs->add('Add User');

        if (!$this->theForm) $this->theForm = new UserForm();
        
        $this->view->setVars([
                            'form' => $this->theForm,
                            'disableHeaderMessage' => TRUE,
                            ]);
        $this->view->pick('user/form');
    }

    /**
     * Create new User
     */
    public function createAction() {
        
        $this->theForm = new UserForm();
        $this->theUser = new User();
              
        if ($this->theForm->isValid($this->request->getPost())) {
            
            $this->_createOrEditAction($this->theUser);
            
            $this->theUser->setEmail_verification_status(0);
            $this->theUser->setDate_created(DateHelper::localToSQL());

            $success = $this->theUser->save();
            
            if ($success) {
            
                $this->theUser = User::findFirst($this->theUser->getId());    // need to re-get the object because of phalcons bug where they default all non-set variables to null (which will blow up if column is NOT NULL)
                $this->theUser->hashPassword($this->request->getPost('passwordTxt'));
                $this->theUser->save();
                
                $this->flash->success("User has been created successfuly.");      
                $this->view->disable();
                return $this->response->redirect($this->url->getBackend());
                
            } else {
                if (isset($this->theUser) && ($errors = $this->theUser->getMessages())  && count($errors)) {
                    foreach ($errors as $error) {
                        $this->flash->error("Error: " . $error->getType() . " - " . $error->getField() . " - " . $error->getMessage());
                    }
                    $this->dispatcher->forward(['action' => 'new']);
                } else {
                    $this->flash->error("Could not create user!");
                    $this->dispatcher->forward(['action' => 'new']);
                }
            }
        }else{
            $this->flash->error("Validation Errors Occured!");
            $this->dispatcher->forward(['action' => 'new']);
        }
    }

    /**
     * Sets set the common form fields for create and edit of the user
     * 
     * @param \App\Model\user $theUser
     */
    private function _createOrEditAction($theUser) {
        
        $theUser->setFirst_name($this->request->getPost('first_name'));
        $theUser->setLast_name($this->request->getPost('last_name'));
        $theUser->setEmail($this->request->getPost('email'));
        $theUser->setState($this->request->getPost('state'));
        $theUser->setStatus($this->request->getPost('status'));
    }

    /**
     * Shows the Edit User form
     */
    public function editAction($id) {

        $this->tag->setTitle('Edit User');
        $this->breadcrumbs->add('User',$this->url->getBackend());
        $this->breadcrumbs->add('Edit User');
                
        if (!$this->theUser) $this->theUser = User::findFirst($id);       
        
        if (!$id || !$this->theUser) {
            $this->flash->error("User not found!");
            $this->view->disable();
            return $this->response->redirect($this->url->getBackend());
        }
        
        if (!$this->theForm) $this->theForm = new UserForm($this->theUser, ['action' => 'edit', 'actionUrl' => $this->url->getBackend('save', $id)]);
        
        $this->view->form = $this->theForm;
        $this->setViewAsModule();
        $this->view->pick('user/form');
    }

    /**
     * Update the user
     */
    public function saveAction($id) {
        
        $theUser = $this->theUser = User::findFirst($id); 
        $this->theForm = new UserForm($theUser, ['action' => 'edit', 'actionUrl' => $this->url->getBackend('save', $id)]);  // needs to be different copy of entity since Phalcon screws up validation on *many relationships
        
        if ($this->theForm->isValid($this->request->getPost())) {
            if (!$id || !$theUser) {
                $this->flash->error("User not found!");
                $this->view->disable();
                return $this->response->redirect($this->url->getBackend());
            }

            $this->_createOrEditAction($theUser);
            if($this->request->getPost('passwordTxt')){
                $this->theUser->hashPassword($this->request->getPost('passwordTxt'));
            }
            $theUser->setDate_modified(DateHelper::localToSQL());
            $success = $theUser->save();

            if ($success) {
                $this->flash->success("User details has been saved successfuly.");
                $this->view->disable();
                return $this->response->redirect($this->url->getBackend());
            } else {
                $errors = $theUser->getMessages();
                foreach ($errors as $error) {
                    $this->flash->error("Error: " . $error->getType() . " - " . $error->getField() . " - " . $error->getMessage());
                }
                $this->dispatcher->forward(['action' => 'edit']);
            }
        } 
        else {
            $this->flash->error("Could not save user!");
            $this->dispatcher->forward(['action' => 'edit']);
        }
    }

    /**
     * Delete the user
     */
    public function deleteAction($id) {
        $theUser = $this->theUser = User::findFirst($id); 
        if (!$id || !$theUser) {
            $this->flash->error("User not found!");
            $this->view->disable();
            return $this->response->redirect($this->url->getBackend());
        }

        if($theUser->delete() === false) {
            $this->flash->error("Could not delete user!");
        }
        else {
            $this->flash->success("User deleted successfuly.");
        }
        $this->response->redirect($this->request->getHTTPReferer());
    }
}
