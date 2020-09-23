<?php

/**
 * Admin New/Edit Test Form Class
 * @author Prakash
 */

namespace App\Modules\Admin\Form;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use App\Library\FormElements\Tag;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

use App\Model\Test;

class TestForm extends CBaseForm {

    protected $moduleName;
    /**
     * Form Element For First Name
     * @elementtype input[text]
     */
    protected function first_name() {
        $element = new Text('first_name');
        $element->setLabel('First Name');
        $element->setAttribute('class', 'form-control');
        $element->setUserOption('lblRequired', true);
        $element->addValidators(array(
            new PresenceOf(array('message' => 'First Name is required'))
        ));
        $this->add($element);
    }
    
    /**
     * Form Element For Last Name
     * @elementtype input[text]
     */
    protected function last_name() {
        $element = new Text('last_name');
        $element->setLabel('Last Name');
        $element->setAttribute('class', 'form-control');
        $element->setUserOption('lblRequired', true);
        $element->addValidators(array(
            new PresenceOf(array('message' => 'Last Name is required'))
        ));
        $this->add($element);
    }


 protected function state() {
        $element = new Text('state');
        $element->setLabel('State Name');
        $element->setAttribute('class', 'form-control');
        $element->setUserOption('lblRequired', true);
        $element->addValidators(array(
            new PresenceOf(array('message' => 'State Name is required'))
        ));
        $this->add($element);
    }
    
    /**
     * Form Element For Email
     * @elementtype input[text]
     */
    protected function email($options) {
        $element = new Text('email');
        $element->setFilters('email');
        $element->setLabel('Email');
        $element->setAttribute('class', 'form-control');
        
        $validators = array( new PresenceOf(array('message' => 'Email is required')) );
        
        if($this->request->isPost() && $this->request->getPost('email')){
            $validators[] = new Email(array('message' => 'E-mail is not valid'));
        }
        if($options['action'] == 'create'){
            $validators[] = new UniquenessValidator(["model"   => new Test(), 'message' => 'Sorry, Email address already registered!!']);
            $element->setUserOption('lblRequired', true);
        }
        $element->addValidators($validators);
        $this->add($element);
    }
     
    /**
     * Form Element For Password
     * @elementtype input[text]
     */   
    protected function password($options) {
        $element = new Password('passwordTxt');
        $element->setLabel('Password');
        $element->setAttribute('class', 'form-control');
        if($options['action'] == 'create' || ($options['action'] != 'create' && $this->request->getPost('passwordTxt')))
        {
            $validators = array( new PresenceOf(array('message' => 'Password is required')) );
            if($this->request->isPost() && $this->request->getPost('passwordTxt')){
                $validators[] = new StringLength(array(
                                            "min"            => 8,
                                            "max"            => 16,
                                            "messageMaximum" => "Password must not be greater than 16 characters",
                                            "messageMinimum" => "Password must be between 8-16 characters",
                                            ));
            }
            $element->addValidators($validators);
            $element->setUserOption('lblRequired', true);
        }
        $this->add($element); 
    }

    /**
     * Form Element For Repeat Password
     * @elementtype input[text]
     */
    protected function repeatPassword($options) {
        $element = new Password('repeatPassword');
        $element->setLabel('Repeat Password');
        $element->setAttribute('class', 'form-control');
        if($options['action'] == 'create' || ($options['action'] != 'create' && $this->request->getPost('passwordTxt')))
        {
            $validators = array( new PresenceOf(array('message' => 'Confirmation Password is required')) );
            if($this->request->isPost() && $this->request->getPost('passwordTxt') && $this->request->getPost('repeatPassword')){
                $validators[] = new Confirmation(array('message' => "Password doesn't match", 'with'  => "passwordTxt"));
            }
            $element->addValidators($validators);
            $element->setUserOption('lblRequired', true);
        }
        $this->add($element); 
    }

    /**
     * Form Element for test status
     * @elementtype select
     */
    private function status() {
        $element = new Select('status');
        $element->setLabel('Status');
        $element->setAttribute('class', 'custom-select col-12');
        $element->setOptions(Test::getStatuses());
        $element->setUserOption('lblRequired', true);
        $this->add($element);
    }
    
    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = null, $options = null) {
        parent::initialize();
        if (!isset($options) || !isset($options['action'])) $options['action'] = 'create';
        if (!isset($options) || !isset($options['actionUrl'])) $options['actionUrl'] = $this->url->getBackend('create');
        if (!isset($options) || !isset($options['cancelAction'])) $options['cancelAction'] = $this->url->getBackendUri();
        
        $this->setAction($options['actionUrl']);
        $this->setEntity($entity);
        $this->setFormOptions($options);
        $this->templateOptions['templateName'] = 'default';
        
        $this->first_name();
        $this->last_name();
        $this->state();
        $this->email($options);
        $this->password($options);
        $this->repeatPassword($options);
        $this->status();
        $this->defaultSubmit(['templateName' => 'buttons']);
        $this->defaultCancel(['templateName' => 'buttons']);
        
    }
}