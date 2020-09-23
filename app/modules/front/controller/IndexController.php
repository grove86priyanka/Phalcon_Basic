<?php

/**
 * IndexController
 *
 * @author amit
 */

namespace App\Modules\Front\Controller;

class IndexController extends CBaseController
{

    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->tag->setTitle('Home');
    }

}
