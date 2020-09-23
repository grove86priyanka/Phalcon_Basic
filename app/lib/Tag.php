<?php

/**
 * HTML tag manager
 *
 * Extending Phalcon\Tag to overwrite some method
 * @author amit
 */

namespace App\Library;

class Tag extends \Phalcon\Tag {

    /**
     * Builds a SCRIPT[type="javascript"] tag
     *
     * @param string|mix $parameters 
     * @param boolean $local
     * @return string
     */
    public static function javascriptInclude($parameters = null, $local = true) {
        $path = null;
        $parameters = is_array($parameters) ? $parameters : [$parameters];
        if (isset($parameters['src'])) {
            $path = $parameters['src'];
            unset($parameters['src']);
        } else {
            if (isset($parameters[0])) {
                $path = $parameters[0];
                unset($parameters[0]);
            }
        }
        $parameters['src'] = $path ? self::addVersionTag($path, 'js', $local) : null;
        return parent::javascriptInclude($parameters, $local);
    }

    /**
     * Builds a LINK[rel="stylesheet"] tag
     *
     * @param string|mix $parameters 
     * @param boolean $local
     * @return string
     */
    public static function stylesheetLink($parameters = null, $local = true) {
        $path = null;
        $parameters = is_array($parameters) ? $parameters : [$parameters];
        if (isset($parameters['src'])) {
            $path = $parameters['src'];
            unset($parameters['src']);
        } else {
            if (isset($parameters[0])) {
                $path = $parameters[0];
                unset($parameters[0]);
            }
        }
        $parameters['href'] = $path ? self::addVersionTag($path, 'css', $local) : null;
        return parent::stylesheetLink($parameters, $local);
    }

    /**
     * Adding the asset version query
     * @param string $path
     */
    public static function addVersionTag($path = null, $resourceType = null, $local = true) {
        if (!$path || !$resourceType)
            return $path;

        $di = \Phalcon\Di::getDefault();
        $config = $di->get('config');
        

        if ($local) {
        // Manully version only for local files
        // For the manually version creating issue many time like some time forgot to change version and due to this we have to create another pull only for the version changes and some time we have to do hotfix due to this issue
        // Now we are add versioning using filemtime. of course It's take more time then manually update but not that much. And we are adding maximum 10 to 15 file so that take minor time in like 0.0015s
            $fileName = realpath(ROOTPATH . 'public' . $path);
            $filemtime = filemtime($fileName);
        }

        $asset = explode('/', $path);
        $asset = end($asset);

        if (!$asset)
            return $path;

        $configAssetVersion = isset($config->assetVersions) ? $config->assetVersions : [];
        if (isset($configAssetVersion[$resourceType]) && ($configResourceVersion = $configAssetVersion[$resourceType])) {
            if (isset($configResourceVersion[$asset]) && ($assetVersion = $configResourceVersion[$asset])) {
                $path .= "?v=" . $assetVersion;
                $isAssetVersion = true;
            }
        }
        
        if (isset($filemtime) && $filemtime) $path .= (isset($isAssetVersion) && $isAssetVersion ? '&' : '?') . "t=" . $filemtime;

        return $path;
    }

}
