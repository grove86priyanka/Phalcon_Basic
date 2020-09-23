<?php

/**
 * Setting Controller
 *
 * @author vishal
 */

namespace App\Modules\Admin\Controller;

class SettingsController extends CBaseController
{

    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->tag->setTitle('Setting');
        $this->breadcrumbs->add('Setting');
    }

}
