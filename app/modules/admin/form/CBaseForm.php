<?php

/**
 * CBaseForm
 * 
 * Base from for admin module
 * Every form should extend this class
 *
 * @author amit
 */

namespace App\Modules\Admin\Form;

use App\Library\CForm;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

abstract class CBaseForm extends CForm {

 	private $searchPageName = '';
    const DEFAULT_NAME = "default";
    const TYPE_SEARCH = "search";
	protected $searchStaticData = [
		"default" => [
            "per_page" => ['24','48','72','96','120'],
        ],

	];


    public function initialize($args = []) {

        $this->templateOptions['mainErrorClass'] = 'has-danger';
        $this->templateOptions['errorClass'] = 'form-control-feedback';

        if(isset($args['formType']) && $args['formType'] = self::TYPE_SEARCH) {
            $this->templateOptions['lblClass'] = 'has-danger';
            $this->templateOptions['elementClass'] = '';
            $this->templateOptions['mainClass'] = 'form-group search-group';
            $this->setTemplate('<div class="{mainClass}">{label}{input}{options}</div>','default');
        }
    }

    /**
     * Form Element For Per Page
     * @elementtype select
     */
    protected function per_page() {

        $formPerPage = [];
        $perPage = $this->getPerPage();
        
        if (count($perPage) > 0) {
            foreach ($perPage as $page) {
                $formPerPage[$page] = $page;
            }
        }
        $element = new Select('per_page_item');
        $element->setOptions($formPerPage);
        $element->setAttribute('class', 'form-control');
        $element->setLabel('Per Pages');
        if($this->request->has('per_page_item') && ($value = $this->request->get('per_page_item')) ) {
            $element->setDefault($value);
        }
        $this->add($element);
    }

    /**
     * Form Element For Keyword search item
     * @elementtype input[text]
     * @required no
     */
    protected function keyword($elementOptions = []) {

    	$options = $this->options;
    	$placeholder = "search kewords";
    	if(isset($elementOptions['placeholder'])) {
    		$placeholder = $elementOptions['placeholder'];
    	}
    	$placeholderText = 
        $element = new Text('keywords');
        $element->setLabel('Search');
        $element->setAttribute('placeholder',$placeholder);
        $element->setAttribute('class', 'form-control');
        if($this->request->has('keywords') && ($value = $this->request->get('keywords')) ) {
            $element->setDefault($value);
        }
        if ($elementOptions)
            $element->setUserOptions($elementOptions);
        $this->add($element);

    }

    protected function setSearchPageName($searchPageName) {
        $this->searchPageName = $searchPageName;
    }

    protected function getSearchPageName() {
        return $this->searchPageName;
    }

    public function defaultSearchSubmit($args = []) {

        $args['submitText'] = isset($args['submitText']) ? $args['submitText'] : 'Search';
        $args['class'] = isset($args['class']) ? $args['class'] : 'btn waves-effect waves-light btn-outline-info';
        $this->defaultSubmit($args);
    }

    public function getPerPage() {
        $searchPageName = $this->getSearchPageName();
        $perPage = (isset($this->searchStaticData[$searchPageName]) && isset($this->searchStaticData[$searchPageName]['per_page']) && !empty($this->searchStaticData[$searchPageName]['per_page']))?$this->searchStaticData[$searchPageName]['per_page']:$this->searchStaticData[$this::DEFAULT_NAME]['per_page'];
        return $perPage;
    }

 	public function getDefaultPerPageVal() {
        $perPage = $this->getPerPage();
        return isset($perPage[0]) ? $perPage[0] : \App\Helper\Utility::DEFAULT_PAGE_SIZE;
    }
}
