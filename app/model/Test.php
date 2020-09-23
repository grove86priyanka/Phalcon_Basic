<?php

/**
 * Model Test
 * @author Amit
 */

namespace App\Model;

use App\Library\CModel;

class Test extends CModel
{

    use \App\Model\Traits\SecurityPasswordModelTrait;

    /**
     * Get AI id
     * @return int
     */
    public function getId()
    {
        return $this->test_id;
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
     * Test status is active (1) or in active (2)
     * @return boolean
     */
    public function isActive() {
        return $this->getStatus() == self::ACTIVE_STATUS_CODE;
    }

    public function afterCreate()
    {
        $this->getLogger()->info('Test Created');
    }

    public function afterDelete()
    {
        $this->getLogger()->info('Test Deleted');
    } 

    /**
     * Generates Encrypted Test Data
     * @param 
     * @return string
     */
    public function getEncryptedTestData(){
        $crypt = \Phalcon\Di::getDefault()->get('crypt');
        
        $cred = ['test_id' => $this->test_id, 'email' => $this->email, 'valid_upto' => time() + (24 * 60 * 60)];
        $serializeCred = serialize($cred);
        return $crypt->encryptBase64URL($serializeCred);
    }

    /**
     * Generates Test Account Verification Link
     * @param 
     * @return Url string
     */
    public function generateVerificaitonLink() {
        $url   = \Phalcon\Di::getDefault()->get('url');
        
        $encrypted = self::getEncryptedTestData();
        return $url->getBackend('verifyemail', $encrypted, 'session', '', '', 'test');
    }

    /**
     * Generates Password Reset Link
     * @param 
     * @return Url string
     */
    public function generateResetLink() {
        $url   = \Phalcon\Di::getDefault()->get('url');
        
        $encrypted = self::getEncryptedTestData();
        return $url->getBackend('reset', $encrypted, 'session', '', '', 'test');
    }


    public static function getTests($params) {
        
        $testQuery = self::query();

        // keywords search
        if (isset($params['keywords']) && ($keywords = $params['keywords']) ) {
            $filterColumns = ['App\Model\Test.first_name', 'App\Model\Test.last_name', 'App\Model\Test.email', 'App\Model\Test.state'];
            if($keywordsQuery = self::basicKeywordLike($keywords, $filterColumns, 'OR')) {
                $testQuery->andWhere($keywordsQuery);
            }
            
        }

        if (isset($params['status']) && ($status = $params['status']) ) {
            $filterColumns = ['App\Model\Test.first_name', 'App\Model\Test.last_name', 'App\Model\Test.email','App\Model\Test.state'];
            $testQuery->andWhere('App\Model\Test.status = :status:',["status" => $status]);
            
        }

        if (isset($params['order'])) $testQuery->orderBy(is_array ($params['order']) ? implode (',', $params['order']) : $params['order']);
        if (isset($params['bind']) && $params['bind']) $testQuery->bind($params['bind']);

        if (isset($params['returnBuilder']) && $params['returnBuilder']) {
           
            $return = $testQuery;
        } else {
            $return = $testQuery->execute();
        }
        return $return;
    }

}
