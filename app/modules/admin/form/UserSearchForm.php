<?php

/**
 * Admin New/Edit User Form Class
 * @author Vishal
 */

namespace App\Modules\Admin\Form;

use App\Library\CForm;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;

use App\Model\User;

class UserSearchForm extends CBaseForm {

    protected $moduleName;

    /**
     * Form Element for user status
     * @elementtype select
     */
    private function status() {
        $statusArr = array(
            '' => 'All',
            User::ACTIVE_STATUS_CODE => 'Active',
            User::INACTIVE_STATUS_CODE => 'Inactive',
        );
        $element = new Select('status');
        $element->setLabel('Status');
        $element->setAttribute('class', 'custom-select form-control');
        if($this->request->has('status') && ($value = $this->request->get('status')) ) {
            $element->setDefault($value);
        }
        $element->setOptions($statusArr);
        $this->add($element);
    }
    
    
    /**
     * Initialize Form
     * @param object $entity optional
     * @param array $options optional
     */
    public function initialize($entity = null, $options = null) {
        parent::initialize(['formType' => self::TYPE_SEARCH]);
        
        $this->setEntity($entity);
        $this->setFormOptions($options);
        $this->templateOptions['templateName'] = 'default';
        
        $this->status();
        $this->keyword();
        $this->per_page();        
        $this->defaultSearchSubmit();        
    }
}