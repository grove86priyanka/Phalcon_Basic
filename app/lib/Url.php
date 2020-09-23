<?php

/**
 * Extension of phalcons url service
 * used for adding backend url generation in a structured way while having the convenience of  it being in the same place as front-end url
 * also will be used for extending or adding more robust front-end url generation (since phalcons route name is way to limiting
 * 
 * @author Vishal
 */

namespace App\Library;

use Phalcon\Mvc\Url as PhalconUrl;

class Url extends PhalconUrl 
{
    protected $parentUriDefaults = [];

    public function getBaseUrl()
    {
        return $this->getDi()->get('config')->baseUrl;
    }
    
    public function getDomain()
    {
        $baseUrl = $this->getBaseUrl();
        $tmp = parse_url($baseUrl);
        return $tmp['host'];
    }
    
    /**
     * allows for setting default parent resource 
     * this allows us to use the same templates/code inside of a parent resource 
     * and outside without having to do if conditions checking if inside or outside all over
     * this keeps the inner code modifications to a minimal to get it to work in both templates/views
     * 
     * @param type $parentResourceName
     * @param type $parentResourceId
     * @param array $for
     */
    public function setBackendDefaults($parentResourceName = null, $parentResourceId = null) {
        
        return $this->parentUriDefaults = ['parentResourceName' => $parentResourceName, 'parentResourceId' => $parentResourceId];
    }
    
    /**
     * gets the current defaults on either if its set for only that resource, or resource part or all 
     */
    public function getBackendDefaults() {
        return $this->parentUriDefaults;
    }
    
    /**
     * generates url for all backend (seller/admin) pages
     * 
     * @param type $action the name of the action
     * @param type $resourceId the id of the associated resource
     * @param type $resourceName the name of the resource (note in the case of parent resource the real controller action will be concat of $resourceName and $action
     * @param type $parentResourceName if this is a sub resource then the parent resource name
     * @param type $parentResourceId the parent resource id
     * @param type $moduleName the module that it should be under, generally this can be left blank and just use the module name from the dispatcher
     * @return string the url
     */
    public function getBackendUri($action = null, $resourceId = null, $resourceName = null, $parentResourceName = null, $parentResourceId = null, $moduleName = null) {
        $di = \Phalcon\Di::getDefault();
        $dispatcher = $di->getShared('dispatcher');
//        $controller = $dispatcher->getActiveController();
        
        $moduleName = (isset($moduleName) ? $moduleName : $dispatcher->getModuleName());
        
        $parentResourceDefaults = $this->getBackendDefaults() ?: ['parentResourceName' => '', 'parentResourceId' => ''];
        
        $parentResourceName = (isset($parentResourceName) ? $parentResourceName : $parentResourceDefaults['parentResourceName']);
        $parentResourceId = (isset($parentResourceId) ? $parentResourceId : $parentResourceDefaults['parentResourceId']);
        
        $resourceName = (isset($resourceName) ? $resourceName : $dispatcher->getControllerName());
        
        $url = '';
        
        if ($moduleName) $url .= "/$moduleName";
        if ($parentResourceName) $url .= "/$parentResourceName";
        if ($parentResourceId) $url .= "/$parentResourceId";
        if ($resourceName) $url .= "/$resourceName";
        if ($action) $url .= "/$action";
        if ($resourceId) $url .= "/$resourceId";
        
        return $url;
    }
    
    public function getBackend(...$args) {
        return $this->get($this->getBackendUri(...$args));
    }
    
    const filterValueDelimiter = '-';    // do not change! will break existing urls!
    const filterValueBase = 62;               // do not change! will break existing urls!
    const filterRangeBeginDelimeter = '[';               // do not change! will break existing urls!
    const filterRangeEndDelimeter = ']';               // do not change! will break existing urls!
    const filterRangeDelimeter = '..';               // do not change! will break existing urls!
    const baseStr = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';   // do not change! will break existing urls!
//    const baseStr = 'pkW30KUQugAx9H5rMnmzPGIXoYeifvjRLw1EFNOh4TDC82t6l7ZBcdJaVqSysb';
    /**
     * Convert a from a given base to base 10.
     *
     * @param  string  $value
     * @param  int     $baseStr
     * @return int
     */
    public static function toBase10($value, $b = 62)
    {
        $limit = strlen($value);
        $result = strpos(static::baseStr, $value[0]);
        for($i = 1; $i < $limit; $i++) {
            $result = $b * $result + strpos(static::baseStr, $value[$i]);
        }
        return $result;
    }
    /**
     * Convert from base 10 to another base.
     *
     * @param  int     $value
     * @param  int     $baseStr
     * @return string
     */
    public static function toBase($value, $b = 62)
    {
        $r = $value  % $b;
        $result = static::baseStr[$r];
        $q = floor($value / $b);
        while ($q) {
            $r = $q % $b;
            $q = floor($q / $b);
            $result = static::baseStr[$r].$result;
        }
        return $result;
    }
    
//echo '<pre>';
////echo str_shuffle(\App\Library\Url::baseStr) . "\r\n\r\n";
//$start = 1000000000;
//for ($i = $start; $i < $start + 1000; $i++) {
//    $pad = 5;
//    $base = 62;
//    $i62 = $this->url->toBase($i, $base);
//    $i10 = $this->url->toBase10($i62, $base);
//    echo str_pad($i, $pad, ' ', STR_PAD_LEFT) . '   ' .
//         str_pad($i62, $pad, ' ', STR_PAD_LEFT) . '   ' .
//         str_pad($i10, $pad, ' ', STR_PAD_LEFT) . '   ' .
//         str_pad(base_convert($i, 10, 36), $pad, ' ', STR_PAD_LEFT) . "\r\n";
//}
//echo '</pre>';
//exit();
    
    public function appendCurrentQuery($url, $exclude = [], $include = [], $request = null) {
        
//        $getParams = $this->request->get() ?: [];
        
        $exclude = array_merge($exclude ?: [], ['_url']);
        
        $getParams = $request ?: $this->getDi()->getRequest()->getQuery() ?: [];
        if ($include) $getParams = array_intersect_key($getParams, array_combine($include, $include));
        if ($exclude) $getParams = array_diff_key($getParams, array_combine($exclude, $exclude));
        if ($getParams) $url .= (strstr($url, '?') ? '&' : '') . ($getParams ? '?' . http_build_query($getParams) : '');
        
        return $url;
    }
    
    public function parseUrl($url) {
        return parse_url($url);
    }
    
    function buildUrl($parsed_url) {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
    
    /**
     * adds (or replaces) a parameter in an existing url
     * ideal for adding query parameters onto urls that you don't know or don't want to rebuild from original generator
     * will replace if parameter already exists, will append if not
     * 
     * @param string $url
     * @param array $params
     * @param boolean $params true will merge new multi-dimensional arrays into existing, false will overwrite multi-dimensional arrays
     */
    public function addParameters($url, $params = [], $recursive = false)
    {
        $queryStr = [];
        $parsedUrl = $this->parseUrl($url);
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryStr);
            if (!$queryStr) $queryStr = [];
        }
        
        if ($recursive) $queryStr = array_replace_recursive($queryStr, $params);
        else $queryStr = array_merge($queryStr, $params);
        
        $parsedUrl['query'] = http_build_query($queryStr);
        
        $url = $this->buildUrl($parsedUrl);
        
        return $url;
    }
    
    /**
     * gets the REQUEST_URI style param option but factors in Phalcon's "baseURI" setting, includes query params
     * otherwise if you base is http://localhost/bidsquare/ you would end up duplicating path http://localhost/bidsquare/bidsquare/
     */
    public function getRequestUri()
    {
        $di = $this->getDi();
        
        $_url = $di->get('request')->getQuery('_url', null, '');
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // if there is no _url param then since it doesn't exist just take the request uri which will already include the query parameter as needed
        if (!$_url) return $requestUri;
        
        // just in case for some strange reason _url has a query param then just remove it since its probably a duplicate of query params from request
        // alternatively we could assume that this query string is correct but I'd rather not since the apache orginal QS is probably more accurate
        if (strpos($_url, '?') !== false) {
            $_url = explode('?', $_url, 2);
            $_url = $_url[0];
        }
        
        // add on the query parameter string if exists
        $requestUri = explode('?', $requestUri, 2);
        if (sizeof($requestUri) > 1) {
            $_url .= '?' . $requestUri[1];
        }
        return $_url;
    }
}
?>