<?php

/**
 * Base Controller Class
 * @author Amit
 */

namespace App\Library;

use Phalcon\Mvc\Controller;
use App\Library\Sort as SortLib;

abstract class CController extends Controller
{

    private $assetsCss = [];
    private $assetsJs = [];
    protected $moduleName;

    /**
     * Implement onConstruct method
     * --------------------------------------------------------
     * onConstruct() method is executed even if the action to be executed doesn't exist in the controller or the user does not have access to it
     * --------------------------------------------------------
     */
    public function onConstruct()
    {
        $this->moduleName = $this->router->getModuleName();

        // Collecting main JS
        $this->addJs('/js/jquery.js');
        $this->addJs('/js/functions.js');
        $this->addJs("/js/bootstrap.min.js");

        // Collection main CSS
        $this->addCss("/css/bootstrap.min.css");
        $this->addCss("/css/font-awesome.css");
    }

    /**
     * Initializing Front Base Controllers Options
     */
    public function initialize()
    {
        if ($this->moduleName === 'admin')
        {
            $this->addCss("/css/admin.css");
            $this->addJs("/js/sidebarmenu.js");
            $this->addCss("/css/sweetalert.css");
            $this->addJs("/js/sweetalert.js");

        }
        if ($this->moduleName === 'front' || $this->moduleName === 'user')
        {
            $this->addCss("/css/font.css");
        }
    }

    /**
     * Add Javascript File to the Assets Queue
     * @param string $path Relative path to the file
     * @param boolean $local (true => local file, false => external file)
     */
    public function addJs($path, $local = true)
    {
        // note, we are now storing the hashes inside assests but we are "keying" it so we don't add it twice
        // we also are adding it immediately instead of deferring until after route complete
        // this allows us to still keep the correct order but not have duplicates
        // this change was needed since we ended up losing the added assets when we forward to another controller (since it is now a new object and the hashed array is lost)
        if (!isset($this->assets->assetsJs))
            $this->assets->assetsJs = [];
        if (!array_key_exists($path, $this->assets->assetsJs))
        {
            $this->assets->assetsJs[$path] = $local;
            $this->assets->collection("header_js")->addJs(Tag::addVersionTag($path, 'js', $local), $local);  // add it here instead of afterExecuteRoute so we can render in the contoller if we need to even though its not done "executing". This will still maintain original order and prevent dups from being added
        }
    }

    /**
     * Add CSS File to the Assets Queue
     * @param string $path Relative path to the file
     * @param boolean $local (true => local file, false => external file)
     */
    public function addCss($path, $local = true)
    {
        // see addJs comments for details
        if (!isset($this->assets->assetsCss))
            $this->assets->assetsCss = [];
        if (!array_key_exists($path, $this->assets->assetsCss))
        {
            $this->assets->assetsCss[$path] = $local;
            $this->assets->collection("header_css")->addCss(Tag::addVersionTag($path, 'css', $local), $local);  // add it here instead of afterExecuteRoute so we can render in the contoller if we need to even though its not done "executing". This will still maintain original order and prevent dups from being added
        }
    }

    /**
     * Set view back to the module
     */
    protected function setViewAsModule()
    {
        $this->view->setViewsDir(APPPATH . 'modules' . DS . $this->moduleName . DS . 'view');
    }

    /**
     * Set Partialview back to the module
     */
    protected function setPartialViewAsModule()
    {
        $this->view->setPartialsDir(APPPATH . 'modules' . DS . $this->moduleName . DS . 'view');
    }

    /**
     * Set Partialview as common
     */
    protected function setPartialViewAsCommon()
    {
        $this->view->setPartialsDir($this->config->view->partialsDir);
    }

    /**
     * Initializing layout
     */
    protected function initializeLayout($layout = 'main')
    {
        $this->view->setTemplateAfter($layout);
    }

    protected function buildSorting($baseUrl, $default_sort = null, $default_sort_asc = null, $ignoreParams = [])
    {
        if ($this->request->has('sort')) {
            $sort = $this->request->get('sort');
            $sort_asc = $this->request->get('sort_asc', null, null);
        } else {
            $sort = $default_sort;
            $sort_asc = $default_sort_asc;
        }
        $ignoreParams = array_merge(['sort', 'sort_asc', 'page'], $ignoreParams ?: []);
        
        $sortBaseUrl = $this->url->appendCurrentQuery($baseUrl, $ignoreParams);
        
        $sortPlugin = new SortLib($sortBaseUrl, $sort, $sort_asc);
        
        return $sortPlugin;
    }

}
