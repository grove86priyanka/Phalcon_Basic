<?php

/**
 * CBaseController
 * 
 * Base controller for user module
 * Every controller should extend this controller
 *
 * @author amit
 */

namespace App\Modules\User\Controller;

use App\Library\CController;

abstract class CBaseController extends CController
{
	protected $theSessionUser = NULL;
    
    public function initialize() {
    	$return = parent::initialize();
    	if($this->user->isOnline()) {
    		$this->theSessionUser = $this->user->getData();
    	}
		$this->view->theSessionUser = $this->theSessionUser;
		return $return;
    }


}
