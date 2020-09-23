<?php

/**
 * PaginationModel Class
 * Use a Phalcon\Mvc\Model\Resultset object as source data.
 * @author Amit
 */

namespace App\Library\Pagination;

use App\Library\Pagination\PaginationBase;
use Phalcon\Paginator\Adapter\Model as PagiModel;
use Phalcon\Paginator\Adapter\QueryBuilder as PagiBuilder;

class PaginationModel extends PaginationBase
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
        if (isset($this->builder))
        {
            $paginate = new PagiBuilder(array(
                "builder" => $this->builder,
                "limit" => $this->limit,
                "page" => $this->currentPage
            ));
        } else
        {
            $paginate = new PagiModel(array(
                "data" => $this->data,
                "limit" => $this->limit,
                "page" => $this->currentPage
            ));
        }
        $this->paginate = $paginate->getPaginate();
    }

}
