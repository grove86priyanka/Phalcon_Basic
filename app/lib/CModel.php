<?php

/**
 * Base Model Class
 * @author Amit
 */

namespace App\Library;

use Phalcon\Mvc\Model;

abstract class CModel extends Model
{

    const ACTIVE_STATUS_CODE = 1;
    const INACTIVE_STATUS_CODE = 2;

    /**
     * @var array store model related alias
     */
    protected $modelRelations;
    protected $logger;
    protected $fixResultSets; // needed for performance so we log which fields are result sets and then only convert them on save

    /**
     * Implement Event onConstruct()
     * Will gather model relation alias
     */

    public function onConstruct()
    {
        $this->useDynamicUpdate(true);  // set for all, without this phalcon sometimes will re-select from database and overwrite your changes if you call save() more than 1 time
        $this->keepSnapshots(true);
        $modelRelationAlias = $this->getModelsManager()->getRelations(get_called_class());
        foreach ($modelRelationAlias as $alias)
        {
            $this->modelRelations[$alias->getType()][] = $alias->getOption('alias');
        }
    }

    /**
     * Handles method calls when a method is not implemented
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        /**
         * If the method starts with "get" we try to get a service with that name
         */
        if (substr($method, 0, 3) == 'get')
        {
            $getProperty = lcfirst(str_replace('get', '', $method));
            if (!method_exists($this, $method))
            {
                return $this->{$getProperty};
            }
        }

        /**
         * If the method starts with "set" we try to set a service using that name
         */
        if (substr($method, 0, 3) == 'set')
        {
            $setProperty = lcfirst(str_replace('set', '', $method));
            if (!method_exists($this, $method))
            {
                return $this->{$setProperty} = isset($arguments[0]) ? $arguments[0] : NULL;
            }
        }

        return parent::__call($method, $arguments);
    }

    public function __set($property, $value)
    {
        /**
         * needed to save many-to-many relationships, apparently phalcon ignores this so they never get saved
         * so now we can do:
         * <code>
         * 
         * $item = Item::findFirst(1);
         * $categories = Category::where([1,2,3]);
         * $item->categories = $categories;
         * $item->save();   // this now sets the item to have categories 1, 2, and 3 before it would just silently ignore
         * 
         * // otherwise we would have had to do
         * ...
         * $categories = Category::where([1,2,3]);
         * $newCats = [];
         * foreach ($categories as $cat) $newCats[] = $cat;
         * $item->categories = $newCats;
         * ...
         * 
         * </code>
         * 
         * solution was found on page http://stackoverflow.com/questions/23374858/update-a-records-n-n-relationships
         */
        // convert from Phalcon\Mvc\Model\Resultset\Simple
        if ($value instanceof \Phalcon\Mvc\Model\ResultSetInterface)
        {
            // converts resultset into array instead
            // note: you would think you could do $value->toArray() but that converts everything to an array, not just the outer part
            // just track the result sets that we have to convert if in the event we are setting the resultset to the property
            // need to do it this way for performance, filter is too slow
            // this gets triggered for every field for every fetch query (but only when using model relationships aliases
//            $this->fixResultSets[$property] = true;
            //
            $value = $value->filter(function($r) {
                return $r;
            });
        } else
        {
            unset($this->fixResultSets[$property]);
        }
        parent::__set($property, $value);
    }

    public function beforeSave()
    {
        // really it **might** be better to iterate over all fields and check those but the check to find the fields might end up being slower than just doing it this way
        if ($this->fixResultSets)
        {
            foreach ($this->fixResultSets as $property => $__)
            {
                if (isset($this->$property) && ($value = $this->$property) instanceof \Phalcon\Mvc\Model\ResultSetInterface)
                {
                    unset($this->fixResultSets[$property]);
                    $this->$property = $value->filter(function($r) {
                        return $r;
                    });
                }
            }
        }
    }

    /**
     * The called class is the model
     * @return Model instance
     */
    protected static function getModel()
    {
        $modelName = get_called_class();
        $model = new $modelName();
        $model->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        return $model;
    }

    /**
     * Abstract Method getId()
     * Every model need to implement this method
     * This method will return the primary id
     */
    abstract public function getId();

    public static function basicKeywordLike($searchStr, $filterColumns = [], $op = 'OR')
    {
        $db = \Phalcon\Di::getDefault()->get('db');
        $cond = $keywords = [];
        $return = false;
        if ($searchStr && $filterColumns && count($filterColumns)) {
            
            $searchStr = trim($searchStr); // trim the blank spaces
            $keywordBindParamsArr = [];
            $keywordArr = explode(' ', $searchStr);
            foreach ($keywordArr as $kw) {
                $keywords[] = "%$kw%";
            }
            foreach ($filterColumns as $columnName) {
                // $columnName = preg_replace('#[^a-z0-9_\[\]\\\.]+#i', '_', $columnName);  // need to allow PDO column style names like [\App\Model\...].[column_name]
                if (is_array($keywords)) {
                    foreach ($keywords as $val) {
                        $cond[] = "$columnName LIKE " . $db->escapeString($val);
                    }
                } else {
                    $cond[] = "$columnName LIKE " . $db->escapeString($this->value);
                }
            }
            if ($cond && $op) $return = '(' . implode(" {$op} ", $cond) . ')';
        }
        return $return;
    }

    /**
     * Overridden to stop throwing invalid snapshot error when snapshot not available
     * Instead of throw error, need to silently return FALSE
     * 
     * @param array|string $fieldName
     * @return type
     */
    public function hasChanged($fieldName = null, $allFields = NULL)
    {
        if ($this->hasSnapshotData())
        {
            return parent::hasChanged($fieldName, $allFields);
        }
        return false;
    }

    /**
     * Get the logger instance within model
     * @return object of Phalcon\Logger\Adapter\File
     */
    public function getLogger()
    {
        if (!$this->logger)
        {
            $this->logger = $this->getDi()->getShared('logger');
        }
        return $this->logger;
    }

    /**
     * Prints Flash Error Message
     * @param string $errmsg Additional error message to be prepended
     */
    public function flashErrors($errorMessage = '')
    {
        $errors = [];
        if ($errorMessage)
            $errors[] = $errorMessage;
        $modelMessages = $this->getMessages();
        if ($modelMessages && count($modelMessages) > 0)
        {
            $flash = \Phalcon\Di::getDefault()->get('flash');
            foreach ($modelMessages as $error)
            {
                $errors[] = "Error: " . $error->getType() . " - " . $error->getField() . " - " . $error->getMessage();
            }
            if ($flash && count($errors))
                $flash->error(implode("<br>", $errors));
        }
    }

}

