<?php

/**
 * Nav Elements Plugins
 * Helps to build navigation for the admin section
 * @author Amit
 */

namespace App\Modules\Admin\Plugins;

use Phalcon\Mvc\User\Plugin;

class Nav extends Plugin {

    /**
     * Top Account Menu
     * @var type array
     */
    private $accountMenu = array(
        'admin' => array(
            'title' => 'Dashboard',
            'link' => '/',
            'controller' => 'index',
            'action' => 'index',
        ),
        'user' => array(
            'title' => 'User',
            'link' => '/user/',
            'controller' => 'user',
            'action' => 'index',
        ),
         'Test' => array(
            'title' => 'Test',
            'link' => '/test/',
            'controller' => 'Test',
            'action' => 'index',
        ),
        'settings' => array(
            'title' => 'Settings',
            'controller' => 'settings',
            'action' => 'index',
            'child' => array(
                'settings' => array(
                    'title' => 'All Settings',
                    'link' => '/settings/',
                    'controller' => 'settings',
                    'action' => 'index',
                ),
            ),
        ),
    );

    /**
     * Callback recursive menu function
     */
    private function getMenuRecursion($mainMenu, $menuCssClass = '') {
        global $menuHtml;
        $menuHtml .= '<nav class="'. ($menuCssClass != '' ? $menuCssClass : 'sidebar-nav') .'">';
        $menuHtml .= '<ul id="sidebarnav">';
        foreach ($mainMenu as $menuKey => $menu) {
            if ($this->admin->isAllowed($menu['controller'], $menu['action'])) {
                $anchorTagAttr = $cssClassList = array();
                $cssClassList[] = $elLIClass = $this->isCurrentMenuClass($menuKey);
                $hasChild = (isset($menu['child']) && count($menu['child']) > 0) ? TRUE : FALSE;
                if (!isset($menu['title']) || !$menu['title']) {
                    $menu['title'] = $menuKey;
                }
                $menuHtml .= '<li data-name="'.$menu['title'].'" class="'. ($hasChild ? $elLIClass : '') .'">';
                if ($hasChild) {
                    $cssClassList[] = "has-arrow";
                    $anchorTagAttr['aria-expanded'] = "false";
                }
                $cssClass = implode($cssClassList, ' ');
                $menuHtml .= $this->tag->linkTo( [ (isset($menu['link']) ? $this->url->get("/admin" . $menu['link']) : 'javascript:void(0);'), $menu['title'] , "aria-expanded" =>"false", "class" => $cssClass." waves-effect waves-dark", $anchorTagAttr] );
                if ($hasChild) {
                    $menuHtml .= '<ul aria-expanded="false" class="collapse">';
                    foreach ($menu['child'] as $childMenuKey => $childMenu) {
                        if ($this->admin->isAllowed($childMenu['controller'], $childMenu['action'])) {
                            $cssClassList = array();
                            $cssClassList[] = $this->isCurrentMenuClass($menuKey,$childMenuKey);
                            $cssClass = implode($cssClassList, ' ');
                            $menuHtml .= '<li class="'.$cssClass.'">';
                            $menuHtml .= $this->tag->linkTo(array((isset($childMenu['link']) ? '/admin' . $childMenu['link'] : '#'), $childMenu['title'], "class" => $cssClass));
                            $menuHtml .= '</li>';
                        }
                    }
                    $menuHtml .= '</ul>';
                }
                $menuHtml .= '</li>';
            }

        }
        $menuHtml .= '</ul></nav>';    
    }

    private function isCurrentMenuClass($menuKey = "",$childMenuKey = "") {
        $controllerName = $this->router->getControllerName();
        $actionName = $this->router->getActionName();
        $currentMenu = $menuKey!="" && isset($this->accountMenu[$menuKey])?$this->accountMenu[$menuKey]:null;
        if($childMenuKey!="" && isset($currentMenu['child']) && count($currentMenu['child']) > 0 && isset($currentMenu['child'][$childMenuKey])) {
            $currentMenu = $currentMenu['child'][$childMenuKey];
        }
        $cssClassList = "";
        if($currentMenu && count($currentMenu) > 0) {
            if($controllerName == $currentMenu['controller'] && $actionName == $currentMenu['action']) {
                $cssClassList = 'active';
            }
            if(isset($currentMenu['child']) && count($currentMenu['child']) > 0) {
                $cssClassList = " ";
                foreach ($currentMenu['child'] as $key => $subMenu) {
                    if($controllerName == $subMenu['controller'] && $actionName == $subMenu['action']) {
                        $cssClassList = 'active';
                    }   
                }
            }
        }
        return $cssClassList;
    }

    /**
     * Account Menu
     * @return string
     */
    public function getAccountMenu() {
        global $menuHtml;
        $this->getMenuRecursion($this->accountMenu);
        return $menuHtml;
    }

}
