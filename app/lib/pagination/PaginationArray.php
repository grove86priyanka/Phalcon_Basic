<?php

/**
 * PaginationArray Class
 * Use a PHP array as source data
 * @author Amit
 */

namespace App\Library\Pagination;

use App\Library\Pagination\PaginationBase;
use Phalcon\Paginator\Adapter\NativeArray as PagiArray;

class PaginationArray extends PaginationBase
{

    /**
     * Constructor for PaginationModel
     * @param array $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Initializing Phalcon Paginator Model
     */
    protected function initialize()
    {
        $paginate = new PagiArray(array(
            "data" => $this->data,
            "limit" => $this->limit,
            "page" => $this->currentPage
        ));
        $this->paginate = $paginate->getPaginate();
    }

}
