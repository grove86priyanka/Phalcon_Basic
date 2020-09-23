<?php

use Phalcon\Loader;

//Create a Loader
$loader = new Loader();
$loader->registerNamespaces(array(
    'App\Model' => APPPATH . 'model' . DS,
    'App\Model\Traits' => APPPATH . 'model' . DS . 'traits' . DS,
    'App\Library' => APPPATH . 'lib' . DS,
    'App\Library\Pagination' => APPPATH . 'lib' . DS . 'pagination' . DS,
    'App\Helper' => APPPATH . 'helper' . DS,
    'App\Modules\User' => APPPATH . 'modules' . DS . 'User' . DS,
));


$loader->register();
