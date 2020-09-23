<?php

/**
 * Test Controller
 *
 * @author vishal
 */

namespace App\Modules\Admin\Controller;

use App\Model\Test;
use App\Modules\Admin\Form\TestForm;
use App\Library\Pagination\PaginationModel as Pagination;
use App\Helper\Date as DateHelper;

class TestController extends CBaseController
{

    protected $theForm;   // used to prevent double init of the form when forwarding on form validation error
    protected $theTest;   // used to prevent double init of the form when forwarding on form validation error

    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->tag->setTitle('Test');
        $this->breadcrumbs->add('Test');
        $this->dispatcher->forward(['action' => 'show']);
    }

    /**
     * Displays the tests list
     */
    public function showAction() {
        $this->tag->setTitle('Tests');
        
        $searchForm = new \App\Modules\Admin\Form\TestSearchForm();

        $params = [];
        if ($where = $this->getFilters()) $params = $where;
        // Sorting
        $sortLib = $this->buildSorting($this->url->getBackendUri('', '', 'test'), 'test_id');
        $sortLib->setMapping([
                                'test_id' => ['App\Model\Test.test_id'],
                                'test_name' => ['App\Model\Test.first_name','App\Model\Test.last_name'],
                                'test_email' => ['App\Model\Test.email'],
                                'test_status'      => ['App\Model\Test.status'],
                            ]);
        if (!($params['order'] = $sortLib->getSort('App\Model\Test'))) unset($params['order']);

        $tests = Test::getTests($params + ['returnBuilder' => true]);
        $limit = ($this->request->has('per_page_item') ? $this->request->get('per_page_item') : $searchForm->getDefaultPerPageVal());
        
        $page = $this->request->get('page', 'int', 1);
        $pager = new Pagination([
            'builder' => $tests,
            'limit' => $limit,
            "page" => $page,
        ]);

        $pager->setIgnoreParams(['page']);
        $pager->setUriPattern($this->url->getBackendUri() . '?page={page}');
        $this->view->pager = $pager;
        $this->view->tests = $pager->getItems();
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
     * Displays the add test form
     */
    public function newAction() {
        
        $this->tag->setTitle('New Test');
        $this->breadcrumbs->add('Test',$this->url->getBackend());
        $this->breadcrumbs->add('Add Test');

        if (!$this->theForm) $this->theForm = new TestForm();
        
        $this->view->setVars([
                            'form' => $this->theForm,
                            'disableHeaderMessage' => TRUE,
                            ]);
        $this->view->pick('test/form');
    }

    /**
     * Create new Test
     */
    public function createAction() {
        
        $this->theForm = new TestForm();
        $this->theTest = new Test();
              
        if ($this->theForm->isValid($this->request->getPost())) {
            
            $this->_createOrEditAction($this->theTest);
            
            $this->theTest->setEmail_verification_status(0);
            $this->theTest->setDate_created(DateHelper::localToSQL());

            $success = $this->theTest->save();
            
            if ($success) {
            
                $this->theTest = Test::findFirst($this->theTest->getId());    // need to re-get the object because of phalcons bug where they default all non-set variables to null (which will blow up if column is NOT NULL)
                $this->theTest->hashPassword($this->request->getPost('passwordTxt'));
                $this->theTest->save();
                
                $this->flash->success("Test has been created successfuly.");      
                $this->view->disable();
                return $this->response->redirect($this->url->getBackend());
                
            } else {
                if (isset($this->theTest) && ($errors = $this->theTest->getMessages())  && count($errors)) {
                    foreach ($errors as $error) {
                        $this->flash->error("Error: " . $error->getType() . " - " . $error->getField() . " - " . $error->getMessage());
                    }
                    $this->dispatcher->forward(['action' => 'new']);
                } else {
                    $this->flash->error("Could not create test!");
                    $this->dispatcher->forward(['action' => 'new']);
                }
            }
        }else{
            $this->flash->error("Validation Errors Occured!");
            $this->dispatcher->forward(['action' => 'new']);
        }
    }

    /**
     * Sets set the common form fields for create and edit of the test
     * 
     * @param \App\Model\test $theTest
     */
    private function _createOrEditAction($theTest) {
        
        $theTest->setFirst_name($this->request->getPost('first_name'));
        $theTest->setLast_name($this->request->getPost('last_name'));
        $theTest->setEmail($this->request->getPost('email'));
        $theTest->setState($this->request->getPost('state'));
        $theTest->setStatus($this->request->getPost('status'));
    }

    /**
     * Shows the Edit Test form
     */
    public function editAction($id) {

        $this->tag->setTitle('Edit Test');
        $this->breadcrumbs->add('Test',$this->url->getBackend());
        $this->breadcrumbs->add('Edit Test');
                
        if (!$this->theTest) $this->theTest = Test::findFirst($id);       
        
        if (!$id || !$this->theTest) {
            $this->flash->error("Test not found!");
            $this->view->disable();
            return $this->response->redirect($this->url->getBackend());
        }
        
        if (!$this->theForm) $this->theForm = new TestForm($this->theTest, ['action' => 'edit', 'actionUrl' => $this->url->getBackend('save', $id)]);
        
        $this->view->form = $this->theForm;
        $this->setViewAsModule();
        $this->view->pick('test/form');
    }

    /**
     * Update the test
     */
    public function saveAction($id) {
        
        $theTest = $this->theTest = Test::findFirst($id); 
        $this->theForm = new TestForm($theTest, ['action' => 'edit', 'actionUrl' => $this->url->getBackend('save', $id)]);  // needs to be different copy of entity since Phalcon screws up validation on *many relationships
        
        if ($this->theForm->isValid($this->request->getPost())) {
            if (!$id || !$theTest) {
                $this->flash->error("Test not found!");
                $this->view->disable();
                return $this->response->redirect($this->url->getBackend());
            }

            $this->_createOrEditAction($theTest);
            if($this->request->getPost('passwordTxt')){
                $this->theTest->hashPassword($this->request->getPost('passwordTxt'));
            }
            $theTest->setDate_modified(DateHelper::localToSQL());
            $success = $theTest->save();

            if ($success) {
                $this->flash->success("Test details has been saved successfuly.");
                $this->view->disable();
                return $this->response->redirect($this->url->getBackend());
            } else {
                $errors = $theTest->getMessages();
                foreach ($errors as $error) {
                    $this->flash->error("Error: " . $error->getType() . " - " . $error->getField() . " - " . $error->getMessage());
                }
                $this->dispatcher->forward(['action' => 'edit']);
            }
        } 
        else {
            $this->flash->error("Could not save test!");
            $this->dispatcher->forward(['action' => 'edit']);
        }
    }

    /**
     * Delete the test
     */
    public function deleteAction($id) {
        $theTest = $this->theTest = Test::findFirst($id); 
        if (!$id || !$theTest) {
            $this->flash->error("Test not found!");
            $this->view->disable();
            return $this->response->redirect($this->url->getBackend());
        }

        if($theTest->delete() === false) {
            $this->flash->error("Could not delete test!");
        }
        else {
            $this->flash->success("Test deleted successfuly.");
        }
        $this->response->redirect($this->request->getHTTPReferer());
    }
}
