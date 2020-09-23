<?php

/**
 * Model User
 * @author Amit
 */

namespace App\Model;

use App\Library\CModel;

class User extends CModel
{

    use \App\Model\Traits\SecurityPasswordModelTrait;

    /**
     * Get AI id
     * @return int
     */
    public function getId()
    {
        return $this->user_id;
    }

 public function getState()
    {
        return $this->state;
    }


    public function getFullName()
    {
        return $this->getFirst_name()." ".$this->getLast_name();
    }

     /**
     * returns an array of status names
     * for use to populate pulldowns
     * @return array|int
     */
    public static function getStatuses ($status = null) {
        $statusArr = array(
            self::ACTIVE_STATUS_CODE => 'Active',
            self::INACTIVE_STATUS_CODE => 'Inactive',
        );
        return ($status && isset($statusArr[$status])) ? $statusArr[$status] : $statusArr;
    }

    public function getStatusText()
    {
        return $this->getStatus() == self::ACTIVE_STATUS_CODE ? 'Active' : 'In Active';
    }

    /**
     * User status is active (1) or in active (2)
     * @return boolean
     */
    public function isActive() {
        return $this->getStatus() == self::ACTIVE_STATUS_CODE;
    }

    public function afterCreate()
    {
        $this->getLogger()->info('User Created');
    }

    public function afterDelete()
    {
        $this->getLogger()->info('User Deleted');
    } 

    /**
     * Generates Encrypted User Data
     * @param 
     * @return string
     */
    public function getEncryptedUserData(){
        $crypt = \Phalcon\Di::getDefault()->get('crypt');
        
        $cred = ['user_id' => $this->user_id, 'email' => $this->email, 'valid_upto' => time() + (24 * 60 * 60)];
        $serializeCred = serialize($cred);
        return $crypt->encryptBase64URL($serializeCred);
    }

    /**
     * Generates User Account Verification Link
     * @param 
     * @return Url string
     */
    public function generateVerificaitonLink() {
        $url   = \Phalcon\Di::getDefault()->get('url');
        
        $encrypted = self::getEncryptedUserData();
        return $url->getBackend('verifyemail', $encrypted, 'session', '', '', 'user');
    }

    /**
     * Generates Password Reset Link
     * @param 
     * @return Url string
     */
    public function generateResetLink() {
        $url   = \Phalcon\Di::getDefault()->get('url');
        
        $encrypted = self::getEncryptedUserData();
        return $url->getBackend('reset', $encrypted, 'session', '', '', 'user');
    }


    public static function getUsers($params) {
        
        $userQuery = self::query();

        // keywords search
        if (isset($params['keywords']) && ($keywords = $params['keywords']) ) {
            $filterColumns = ['App\Model\User.first_name', 'App\Model\User.last_name', 'App\Model\User.email', 'App\Model\User.state'];
            if($keywordsQuery = self::basicKeywordLike($keywords, $filterColumns, 'OR')) {
                $userQuery->andWhere($keywordsQuery);
            }
            
        }

        if (isset($params['status']) && ($status = $params['status']) ) {
            $filterColumns = ['App\Model\User.first_name', 'App\Model\User.last_name', 'App\Model\User.email','App\Model\User.state'];
            $userQuery->andWhere('App\Model\User.status = :status:',["status" => $status]);
            
        }

        if (isset($params['order'])) $userQuery->orderBy(is_array ($params['order']) ? implode (',', $params['order']) : $params['order']);
        if (isset($params['bind']) && $params['bind']) $userQuery->bind($params['bind']);

        if (isset($params['returnBuilder']) && $params['returnBuilder']) {
           
            $return = $userQuery;
        } else {
            $return = $userQuery->execute();
        }
        return $return;
    }

}
