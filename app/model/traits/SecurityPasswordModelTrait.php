<?php

/**
 * Trait for use inside a model to support password hashes
 * it requires that the record to exist ($this->getId() must return the id)
 * it also requires the table to have field `password` (should be VARCHAR(255) even though the current php min requirement is VARCHAR(60))
 * 
 * @author Eli
 */

namespace App\Model\Traits;

trait SecurityPasswordModelTrait
{

    /**
     * Creates a password hash and sets it into the `password`
     * 
     * @param string $password  the plain text password
     * @return string the new password hash
     * @throws SecurityPasswordModelTrait\Exception\MissingIdException
     */
    public function hashPassword($password)
    {

        if (!$this->getId())
            throw new SecurityPasswordModelTrait\Exception\MissingIdException('Could not create hash: Missing id for model, must save() model before hashing.');

        $di = \Phalcon\Di::getDefault();
        $config = $di->getShared('config');
        $security = $di->getShared('security');

        $hash = $security->hash($password . $this->getId() . $config->nacl);

        $this->setPassword($hash);

        return $hash;
    }

    /**
     * Checks if a plain text password matches the stored hash in the `password` field
     * 
     * @param string $password the plain text password
     * @param boolean $autoSaveIfLegacy if outdated hash then update to a new hash and save automatically. This will ensure that we always have the most secure hash in the db.
     * @return boolean
     * @throws SecurityPasswordModelTrait\Exception\MissingIdException
     */
    public function checkPasswordHash($password, $autoSaveIfLegacy = false)
    {

        if (!$this->getId())
            throw new SecurityPasswordModelTrait\Exception\MissingIdException('Could not create hash: Missing id for model, must save() model before hashing.');

        $di = \Phalcon\Di::getDefault();
        $config = $di->getShared('config');
        $security = $di->getShared('security');

        $return = $security->checkHash($password . $this->getId() . $config->nacl, $this->password);

        // auto update if its an old hash
        if ($return && $autoSaveIfLegacy && $this->isPasswordLegacyHash())
        {
            $this->hashPassword($password);
            $this->save();
        }

        return $return;
    }

    /**
     * Checks if the stored hash is outdated (either algorithm or work factor)
     * 
     * @return boolean
     */
    public function isPasswordLegacyHash()
    {
        $security = \Phalcon\Di::getDefault()->getShared('security');
        return $security->isLegacyHash($this->password);
    }

}

namespace App\Model\Traits\SecurityPasswordModelTrait\Exception;

class Exception extends \Exception
{
    
}

class MissingIdException extends Exception
{
    
}
