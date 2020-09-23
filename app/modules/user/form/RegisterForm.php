<?php

/**
 * User Login Form Class
 * @author Vishal
 */

namespace App\Modules\User\Form;

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

use App\Model\User;

class RegisterForm extends CBaseForm
{

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

     /**
     * Form Element For Email
     * @elementtype input[text]
     */
    protected function email() {
        $element = new Text('email');
        $element->setFilters('email');
        $element->setLabel('Email');
        $element->setAttribute('class', 'form-control');
        
        $validators[] = new Email(array('message' => 'E-mail is not valid'));
        $validators[] = new UniquenessValidator(["model"   => new User(), 'message' => 'Sorry, Email address already registered!!']);
        $element->setUserOption('lblRequired', true);
        $element->addValidators($validators);
        $this->add($element);
    }
     
    /**
     * Form Element For Password
     * @elementtype input[text]
     */   
    protected function password() {
        $element = new Password('passwordTxt');
        $element->setLabel('Password');
        $element->setAttribute('class', 'form-control');
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
        $this->add($element); 
    }

    /**
     * Form Element For Repeat Password
     * @elementtype input[text]
     */
    protected function repeatPassword() {
        $element = new Password('repeatPassword');
        $element->setLabel('Repeat Password');
        $element->setAttribute('class', 'form-control');
        $validators = array( new PresenceOf(array('message' => 'Confirmation Password is required')) );
        if($this->request->isPost() && $this->request->getPost('passwordTxt') && $this->request->getPost('repeatPassword')){
            $validators[] = new Confirmation(array('message' => "Password doesn't match", 'with'  => "passwordTxt"));
        }
        $element->addValidators($validators);
        $element->setUserOption('lblRequired', true);
        $this->add($element); 
    }

    /**
     * Initialize Form
     */
    public function initialize($entity = null, $options = null) {

        parent::initialize();

        $this->setEntity($entity);
        $this->setFormOptions($options);

        // $defaultTemplate = '<div class="{mainClass} {mainErrorClass}"><div class="{elementClass}"> {input}{options}{error} </div></div>';
        // $this->setTemplate($defaultTemplate,'default');
        
        
        $this->first_name();
        $this->last_name();
        $this->email();
        $this->password();
        $this->repeatPassword();
        $this->defaultSubmit(['templateName' => 'buttons']);
    }

}
