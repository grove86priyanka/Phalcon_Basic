<?php

/**
 * User Login Form Class
 * @author Vishal
 */

namespace App\Modules\User\Form;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;

use App\Model\User;

class RequestSetupForm extends CBaseForm {


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
        $element->setUserOption('lblRequired', true);
        $element->addValidators($validators);
        $this->add($element);
    }
     
    /**
     * Initialize Form
     */
    public function initialize($entity = null, $options = null) {

        parent::initialize();

        $this->setEntity($entity);
        $this->setFormOptions($options);

        $this->email();
        $this->defaultSubmit(['templateName' => 'buttons']);
    }

}
