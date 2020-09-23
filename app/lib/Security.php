<?php

namespace App\Library;

class Security extends \Phalcon\Security
{

    protected $_algo = PASSWORD_DEFAULT;

    const ALGORITHM_DEFAULT = PASSWORD_DEFAULT;
    const ALGORITHM_BCRYPT = PASSWORD_BCRYPT;

    /**
     * Sets algorithm to use, currently only default and bcrypt
     * should be one of php's PASSWORD_* constants or the class constants
     * 
     * @param type $algo
     */
    public function setAlgorithm($algo)
    {
        $this->_algo = $algo;
    }

    /**
     * Gets the current algorithm
     */
    public function getAlgorithm()
    {
        return $this->_algo;
    }

    /**
     * checks a plaintext string against the hash string
     * 
     * @param string $password
     * @param string $passwordHash
     * @param int $maxPassLength
     * @return bool true on match or false on no match
     */
    public function checkHash($password, $passwordHash, $maxPassLength = 0)
    {
        return password_verify($password, $passwordHash);
    }

    /**
     * hashes a plain text password into a 1-way hash
     * 
     * @param type $password
     * @param type $workFactor
     * @return string
     */
    public function hash($password, $workFactor = 0)
    {
        if (!$workFactor)
            $workFactor = $this->getWorkFactor();
        $options = ['cost' => $workFactor];

        $algo = $this->getAlgorithm();

        return password_hash($password, $algo, $options);
    }

    /**
     * Checks if a hash is a current algorithm/work factor, wraps password_needs_rehash with our class settings
     * 
     * Had to overload this function because Phalcon's version always returned false no matter what was passed to it
     * because of this bug we had to re-implement checkHash() and hash() to ensure we will be defaulting to the same algorithm (since phalcon has no way to get what algorithms is used)
     * without this we might always be auto-updating the password every time since it might always report that it is outdated due to different defaults
     * 
     * @param type $passwordHash
     * @return boolean
     */
    public function isLegacyHash($passwordHash)
    {
        $options = ['cost' => $workFactor = $this->getWorkFactor()];

        $algo = $this->getAlgorithm();

        return password_needs_rehash($passwordHash, $algo, $options);
    }

}
