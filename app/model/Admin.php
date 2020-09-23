<?php

/**
 * Model Admin
 * @author Amit
 */

namespace App\Model;

use App\Library\CModel;

class Admin extends CModel {

    use \App\Model\Traits\SecurityPasswordModelTrait;

    /**
     * Admin status is active (1) or in active (2)
     * @return boolean
     */
    public function isActive() {
        return $this->getStatus() == self::ACTIVE_STATUS_CODE;
    }

    /**
     * Get AI id
     * @return int
     */
    public function getId() {
        return $this->admin_id;
    }

    /**
     * Remove Record
     */
    public function deleteRecord() {
        $this->delete();
    }

}
