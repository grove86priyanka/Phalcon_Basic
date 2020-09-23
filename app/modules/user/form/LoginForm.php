<?php

/**
 * User Login Form Class
 * @author Vishal
 */

namespace App\Modules\User\Form;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Forms\Element\Submit;

class LoginForm extends CBaseForm
{

    /**
     * Form Element For Username
     * @elementtype input[text]
     */
    private function username()
    {
        $element = new Text('username');
        $element->setLabel('Username');
        $element->setAttribute('class', 'form-control');
        $element->setAttribute('placeholder', 'Username');
        $element->addValidators(array(
            new PresenceOf(array('message' => 'Username is required'))
        ));
        $this->add($element);
    }

    /**
     * Form Element For Password
     * @elementtype input[password]
     */
    private function password()
    {
        $element = new Password('password');
        $element->setLabel('Password');
        $element->setAttribute('class', 'form-control');
        $element->setAttribute('placeholder', 'Password');
        $element->addValidators(array(
            new PresenceOf(array('message' => 'Password is required'))
        ));
        $this->add($element);
    }

    /**
     * Form Element For Login Buttom
     * @elementtype Button[Submit]
     */
    private function logIn()
    {
        $element = new Submit('login');
        $element->setAttribute('class', 'btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light');
        $element->setUserOption('mainClass', 'form-group text-center m-t-20');
        $this->add($element);
    }

    /**
     * Initialize Form
     */
    public function initialize($entity = null, $options = null)
    {   
        parent::initialize();

        $this->setEntity($entity);
        $this->setFormOptions($options);
        $defaultTemplate = '<div class="{mainClass} {mainErrorClass}"><div class="{elementClass}"> {input}{options}{error} </div></div>';
        $this->setTemplate($defaultTemplate,'default');
        $this->templateOptions['templateName'] = 'default';
        $this->templateOptions['mainClass'] = 'form-group';
        $this->templateOptions['elementClass'] = 'col-xs-12';
        
        $this->username();
        $this->password();
        $this->logIn();
    }

}
