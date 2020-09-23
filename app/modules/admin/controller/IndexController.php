<?php

/**
 * IndexController
 *
 * @author amit
 */

namespace App\Modules\Admin\Controller;

class IndexController extends CBaseController
{

    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->tag->setTitle('Dashboard');
        $this->breadcrumbs->add('Dashboard');
    }

}
