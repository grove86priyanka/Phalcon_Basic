<?php

/**
 * Base Form Class
 * @author Amit
 */

namespace App\Library;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Hidden;

abstract class CForm extends Form
{

    protected $options;
    protected $templateOptions = [
        'lblClass' => 'col-sm-3 col-form-label text-right',
        'lblErrorClass' => '',
        'mainClass' => 'form-group row',
        'mainErrorClass' => 'has-error',
        'elementErrorClass' => '',
        'elementClass' => 'col-sm-9',
        'errorClass' => 'help-block error-message',
        'template' => '<div class="{mainClass} {mainErrorClass}">{label}<div class="{elementClass}">{input}{options}{error}</div></div>',
        // stored templates, can be referenced by setting templateName
        'templates' => [
            'default' => '<div class="{mainClass} {mainErrorClass}">{label}<div class="{elementClass}">{input}{options}{error}</div></div>',
            'inline' => '<span class="{mainClass} {mainErrorClass}">{label}<span class="{elementClass} width-auto">{input}{options}{error}</span></span>',
            'checkbox' => '<span class="{mainClass} {mainErrorClass}"><span class="{elementClass}">{input}{label}{options}{error}</span></span>',
            'buttons' => '{input}',
        ]
    ];
    public static $defniedStates = [
        223 => [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AS' => 'America Samoa',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia',
            'FM' => 'Micronesia1',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'GU' => 'Guam',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MH' => 'Islands1',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PW' => 'Palau',
            'PA' => 'Pennsylvania',
            'PR' => 'Puerto Rico',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VI' => 'Virgin Island',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming'
        ],
        38 => [
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NL' => 'Newfoundland and Labrador',
            'NS' => 'Nova Scotia',
            'NT' => 'Northwest Territories',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon'
        ]
    ];

    protected function setFormOptions($options = null)
    {
        $this->options = $options;
    }

    public function setFormOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function getFormOptions($option = null)
    {
        $options = $this->options;
        if ($option)
            return (isset($options[$option]) ? $options[$option] : null);
        else
            return $options;
    }

    public function setTemplate($template, $name = 'default')
    {
        $this->templateOptions['templates'][$name] = $template;
    }

    public function getTemplate($name = 'default')
    {
        return isset($this->templateOptions['templates'][$name]) ? $this->templateOptions['templates'][$name] : null;
    }

    // get form fied with custom templating 
    public function renderField($name, $attributes = array(), $elementData = array(), $return = false)
    {

        // merge in userOptions to elementData to allow element specific data to be used without the need for passing it as a pramater
        $element = $this->get($name);
        $userOptions = $element->getUserOptions();
        $formOptions = $this->getFormOptions();
        // get options in order of precedence. first is param, next is ueser option, next is form options and last is defualts (defaults eliminates the need to check isset on each usage)
        $elementData = (is_array($elementData) ? $elementData : []) +
                (is_array($userOptions) ? $userOptions : []) +
                (is_array($formOptions) ? $formOptions : []) +
                $this->templateOptions;
        $attributes = (is_array($attributes) ? $attributes : []) + (($elementAttributes = $element->getAttributes()) ? $elementAttributes : []);
        $template = (isset($elementData['templateName']) ? $this->getTemplate($elementData['templateName']) : $elementData['template']);

        $errData = "";
        $tmpMainErrClass = "";
        //Get element
        //$element = $this->get($name);
        //Get error Message
        $messages = $this->getMessagesFor($name);

        if (count($messages) > 0)
        {
            // Label error class set
            $elementData['lblClass'] .= " " . $elementData['lblErrorClass'];
            $tmpMainErrClass = $elementData['mainErrorClass'];

            // set Attribute details
            $cssClass = $element->getAttribute('class');
            if (!isset($attributes['class']))
            {
                $attributes['class'] = '';
            }
            $attributes['class'] .= ' ' . $cssClass . ' ' . $elementData['elementErrorClass'];
            foreach ($messages as $message)
            {
                // parse value of Error Template 
                $errData .= '<span class="' . $elementData['errorClass'] . '">' . $message . '</span>';
            }
        }
        // Set Default Attribute
        $attributes['autocomplete'] = 'off';

        // parse Error
        $template = str_replace('{mainClass}', $elementData['mainClass'] . ' el-' . preg_replace('#[^a-z0-9]+#i', '-', $name), $template);
        $template = str_replace('{mainErrorClass}', $tmpMainErrClass, $template);
        $template = str_replace('{elementClass}', $elementData['elementClass'], $template);
        $template = str_replace('{error}', $errData, $template);

        // parse label
        $elementId = (isset($attributes['id']) && $id = $attributes['id']) ? $id : $element->getName();

        $lblRequiredHTML = isset($elementData['lblRequired']) && $elementData['lblRequired'] ? "<span class='red_t'>*</span>" : ""; // Pass lblRequired = true for the set asterisk in label
        $template = str_replace('{label}', '<label for="' . $elementId . '" class="' . $elementData['lblClass'] . '">' . $element->getLabel() . $lblRequiredHTML . '</label>', $template);
        // parse Element
        $strElement = $this->render($name, $attributes);

        // mainly added for checkbox/radios so we can add text next to it 
        if (isset($elementData['elementText']))
        {
            $strElement = '<label>' . $strElement . $elementData['elementText'] . '</label>';
        }

        ob_start();
        $this->getElementOptions($name);
        $elementOptions = ob_get_clean();
        //$strElement = $this->renderElement($name, $attributes);
        $template = str_replace('{input}', $strElement, $template);
        $template = str_replace('{options}', $elementOptions, $template);

        if ($return)
            return $template;
        else
            echo $template;
    }

    /**
     * It's check current form error, If found any error in form it's return string
     * @return string html
     */
    public function getRequiredMsg($extraClass = "")
    {
        $html = "";
        if ($this->getMessages() && count($this->getMessages()) > 0)
        {
            $html = '<div class="' . $extraClass . ' required_msg color_black01 font_klavika_r font14">
                <span class="color_red01 font_klavika_l font16">*</span>Required fields
            </div>';
        }
        return $html;
    }

    public function getElementOptions($name)
    {
        $element = $this->get($name);
        $userOptions = $element->getUserOptions();
        $messages = $this->getMessagesFor($name);
        if ($userOptions && count($userOptions) > 0)
        {
            if (isset($userOptions['hints']))
            {
                echo '<span class="hints-block help">' . $userOptions['hints'] . '</span>';
            }
            if (isset($userOptions['required_sign']) && count($messages) > 0)
            {
                echo '<span class="required_sign">*</span>';
            }
            if (isset($userOptions['appendInlineJs']))
            {
                echo '<script type="text/javascript">';
                echo '$(function(){';
                echo $userOptions['appendInlineJs'];
                echo '});';
                echo '</script>';
            }
        }
    }

    /**
     * Returns the form's action
     * This will return the absolute url
     */
    public function getAction()
    {
        $formAction = $this->_action;
        if (!$formAction)
        {
            $formAction = $this->router->getRewriteUri();
        }
        if (!preg_match_all('%^(((http|https)?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i', $formAction))
        {
            $formAction = $this->url->get($formAction);
        }
        return $formAction;
    }

    /**
     * Form Element For Default Submit Button
     * @elementtype input[submit]
     */
    protected function defaultSubmit($elementOptions = [])
    {

        $options = $this->options;

        if (isset($options['action']))
            $action = $options['action'];
        else
            $action = ''; //$this->getAction();


        if (isset($elementOptions['submitText']))
            $value = $elementOptions['submitText'];
        else if (in_array(strtolower($action), array('create', 'save')))
            $value = ucfirst($action);
        else
            $value = 'Submit';

        $class = 'btn btn-info';
        if(isset($elementOptions['class'])) {
            $class = $elementOptions['class'];
            unset($elementOptions['class']);
        }

        $element = new Submit('submit');
        $element->setAttribute('id', 'submit');
        $element->setAttribute('name', 'submit');
        $element->setAttribute('value', $value);
        $element->setAttribute('class', $class);
        $element->setUserOption('templateName','buttons');
        if ($elementOptions)
            $element->setUserOptions($elementOptions);
        $this->add($element);
    }

    /**
     * Default Form Element For Cancel Button
     * @elementtype Anchor[Cancel]
     */
    protected function defaultCancel($elementOptions = []) {

        $options = $this->options;

        if (!isset($options['cancelAction']))
            return;

        $cancelAction = $options['cancelAction'];
        $cancelText = (isset($options['cancelText']) ? $options['cancelText'] : 'Cancel');

        $element = new \App\Library\FormElements\Tag('cancel', $elementOptions, 'linkTo', array($cancelAction, $cancelText, 'class' => 'btn btn-danger cancel'));

        $element->setUserOption('templateName','buttons');

        if ($elementOptions)
            $element->setUserOptions($elementOptions);
        $this->add($element);
    }

    /**
     * creates hidden input elements from query parameters so we can persist them
     * should only be used when form is a GET, when its a post you should really just add them to the url
     */
    public function getPersist($igonore = null, $include = null)
    {
        $params = $this->request->get();

        if (isset($params['_url']))
            unset($params['_url']);

        $ignoreParams = array_combine($ignoreParams, $ignoreParams);
        $includeParams = array_combine($includeParams, $includeParams);

        $newParams = [];

        if (count($params) > 0)
        {

            foreach ($params as $key => $value)
            {
                if (!$this->has($key) && ((!$includeParams && !isset($ignoreParams[$key])) || isset($include[$key])))
                {
                    $newParams[$key] = $value;
                }
            }
        }

        return $newParams;
    }

    public function getPersistQuery()
    {
        $params = $this->getPersist();
        $query = '';

        if ($params)
            $query = http_build_query($params);

        return $query;
    }

    public function renderPersistHidden()
    {
//        $params = $this->getPersist();
        $params = $this->getPersistQuery(); // need the query sting to handle arrays and nested arrays properly

        $elements = [];

        if ($params)
        {
            $params = explode('&', $params);
            foreach ($params as $key => $value)
            {

                list($key, $value) = explode('=', $value);
                $key = urldecode($key);
                $value = urldecode($value);

                $element = new Hidden($key);
                $element->setDefault($value);
                $elements[] = $element->render();
            }
        }

        return implode($elements);
    }

    /**
     * Form Element For Default Country Field
     * @elementtype select
     */
    protected function defaultCountry($entity = null, $elementOptions = [], $return = false)
    {
        if (!isset($elementOptions['template']))
            $elementOptions['template'] = '{input}{options}{error}';
        $formAction = $this->getAction();

        $id = "country";
        $label = "Country";
        if (isset($elementOptions['id']))
            $id = $elementOptions['id'];
        unset($elementOptions['id']);
        if (isset($elementOptions['label']))
            $label = $elementOptions['label'];
        unset($elementOptions['label']);
        if (isset($elementOptions['actionPost']) && $elementOptions['actionPost'])
            $formAction = $elementOptions['actionPost'];
        unset($elementOptions['actionPost']);

        $class = "select-box";
        if (isset($elementOptions['class']))
            $class = $elementOptions['class'];
        unset($elementOptions['class']);

        if (!isset($elementOptions['appendInlineJs']))
        {
            $formAction = $this->getAction();
            $elementOptions['appendInlineJs'] = "$(document).on('change', '#$id', function(){
                var thisObj = $(this);
                var dataSerialize = $(this).closest('form').serializeArray();
                dataSerialize.push({name: 'is_ajaxChange', value: true});
                $.post('$formAction', dataSerialize, function (data) {
                    var postData = $(data);
                    var stateElement = postData.find('.el-$id-state');
                    var defaultCode = postData.find('#country_code').data('default-code');
                    if (stateElement.find('input[type=text]').length > 0) {
                        stateElement.find('input[type=text]').val('');
                    }
                    if (stateElement.find('select option:selected').length > 0) {
                        stateElement.find('select option:selected').prop('selected', false);
                    }
                    thisObj.closest('form').find('.el-$id-state').replaceWith(stateElement);
                    thisObj.closest('form').find('#country_code').val(defaultCode);
                });
            });";
        }

        $countryData = $entity;
        if (!$countryData)
        {
            $countryData = \App\Model\Country::find();
        }

        $emptyText = "Select Country";
        if (isset($elementOptions['emptyText']))
            $emptyText = $elementOptions['emptyText'];
        unset($elementOptions['emptyText']);

        $element = new \Phalcon\Forms\Element\Select($id, $countryData, [
            'using' => ['country_id', 'name'],
            'useEmpty' => true,
            'emptyText' => $emptyText
        ]);
        $element->setLabel($label);
        $element->setAttribute('class', $class);
        $element->addValidators(array(
            new \Phalcon\Validation\Validator\PresenceOf(array('message' => 'Country is required')),
        ));
        if (count($elementOptions) > 0)
            $element->setUserOptions($elementOptions);
        $this->add($element);
        if ($return)
        {
            return $element;
        }
    }

    /**
     * Form Element For Default Submit Button
     * @elementtype input[submit]
     */
    protected function defaultState($elementOptions = [], $return = false)
    {
        $id = "state";
        $isAjax = false;
        $label = $placeholder = "Province / State / County";
        if (isset($elementOptions['id']))
            $id = $elementOptions['id'];
        unset($elementOptions['id']);
        if (isset($elementOptions['label']))
            $label = $elementOptions['label'];
        unset($elementOptions['label']);
        if (isset($elementOptions['placeholder']))
            $placeholder = $elementOptions['placeholder'];
        unset($elementOptions['placeholder']);
        if (isset($elementOptions['is_ajaxChange']))
            $isAjax = $elementOptions['is_ajaxChange'];
        unset($elementOptions['is_ajaxChange']);

        $class = "state_area";
        if (isset($elementOptions['class']))
            $class = $elementOptions['class'];
        unset($elementOptions['class']);

        $countryFieldId = "country";
        if (isset($elementOptions['countryFieldId']))
            $countryFieldId = $elementOptions['countryFieldId'];
        unset($elementOptions['countryFieldId']);

        $elementClass = "el-" . $countryFieldId . "-state";
        if (!isset($elementOptions['elementClass']))
            $elementOptions['elementClass'] = '';
        $elementOptions['elementClass'] .= " $elementClass";

        $elementIsSelect = $isRequired = false;
        $selectedCountryId = 0;
        $selectedStateName = '';
        if ($this->request->isPost())
        {
            $selectedCountryId = $this->request->getPost($countryFieldId);
            if (array_key_exists($selectedCountryId, self::$defniedStates))
            {
                $elementIsSelect = $isRequired = true;
                $selectedStateName = $this->request->getPost($id);
            }
        } else
        {
            if (isset($elementOptions['elementIsSelect']) && $elementOptions['elementIsSelect'])
            {
                $elementIsSelect = $isRequired = true;
                if (isset($elementOptions['selectedCountryId']))
                    $selectedCountryId = $elementOptions['selectedCountryId'];
            }
        }

        if ($elementIsSelect)
        {
            $class = "select-box form-control";
            if (isset($elementOptions['selectTemplate']))
                $elementOptions['template'] = $elementOptions['selectTemplate'];
            $element = new \Phalcon\Forms\Element\Select($id, [], ['useEmpty' => true, 'emptyText' => 'Select State']);
            if ($selectedCountryId > 0 && isset(self::$defniedStates[$selectedCountryId]))
                $element->setOptions(self::$defniedStates[$selectedCountryId]);
            if ($selectedCountryId > 0 && $selectedStateName)
            {
                $element->setDefault($selectedStateName);
            }
        } else
        {
            $element = new \Phalcon\Forms\Element\Text($id);
            $element->setAttribute('placeholder', $placeholder);
        }
        $element->setLabel($label);
        $element->setAttribute('class', $class);
        if ($isRequired && !$isAjax)
        {
            $element->addValidators(array(
                new \Phalcon\Validation\Validator\PresenceOf(array('message' => 'State is required')),
            ));
        }
        if (count($elementOptions) > 0)
            $element->setUserOptions($elementOptions);
        $this->add($element);
        if ($return)
        {
            return $element;
        }
    }

}
