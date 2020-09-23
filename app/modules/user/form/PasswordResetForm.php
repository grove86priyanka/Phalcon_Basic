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

class PasswordResetForm extends CBaseForm
{

    /**
     * Form Element For Password
     * @elementtype input[text]
     */   
    protected function password() {
        $element = new Password('password');
        $element->setLabel('Password');
        $element->setAttribute('class', 'form-control');
        $validators = array( new PresenceOf(array('message' => 'Password is required')) );
        $validators[] = new StringLength(array(
                                    "min"            => 8,
                                    "max"            => 16,
                                    "messageMaximum" => "Password must not be greater than 16 characters",
                                    "messageMinimum" => "Password must be between 8-16 characters",
                                    'allowEmpty' => true
                                    ));
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
        if($this->request->isPost() && $this->request->getPost('password') && $this->request->getPost('repeatPassword')){
            $validators[] = new Confirmation(array('message' => "Password doesn't match", 'with'  => "password"));
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
        $this->password();
        $this->repeatPassword();
        $this->defaultSubmit(['templateName' => 'buttons']);
    }

}
