<?php

/**
 * Library for Pagination
 * @author Amit
 */

namespace App\Library\Pagination;

use Phalcon\Mvc\User\Component;
use Phalcon\Http\Request;

abstract class PaginationBase extends Component
{

    protected $data = null;
    protected $limit = 10;
    protected $currentPage = 0;
    protected $previousPage = 0;
    protected $nextPage = 0;
    protected $maxButtonCount = 11;
    protected $nextPageLabel = '&#x3E;'; //<span aria-hidden="true">&raquo;</span>
    protected $prevPageLabel = '&#x3C;'; //<span aria-hidden="true">&laquo;</span>
    protected $firstPageLabel = '&#x3C;&#x3C;'; //<span aria-hidden="true">&larr;</span>
    protected $lastPageLabel = '&#x3E;&#x3E;'; //<span aria-hidden="true">&rarr;</span>
    protected $buttonLinkClass = 'page-link';
    protected $urlPattern = '';
    protected $pageQuery = '';
    protected $ignoreParams = '';
    protected $paginate = null;
    protected $items = null;
    protected $totalItems = null;
    protected $totalPages = 0;
    protected $maxPages = 0;    // artificially restrict page number to lower than calculated max (allows showing correct count but won't let you page past this page), useful for resticting page but still allowing counts for filters to make sense
    protected $isFrontPagination = false;
    protected $pageNumberDropdown = false;
    protected $isFirstLastPagi = true;
    protected $index = 0;

    /**
     * Constructor for PaginationBase
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->setConfig($config);
        $this->initialize();
        if ($this->totalItems !== null)
        {
            $this->items = $this->data; // assume the data is already limited since we passed total
            $this->totalPages = ceil($this->totalItems / $this->limit);
        } else
        {
            $this->items = $this->paginate->items;
            $this->totalItems = $this->paginate->total_items;
            $this->totalPages = $this->paginate->total_pages;
            $this->currentPage = $this->paginate->current;
            $this->previousPage = $this->paginate->before;
            $this->nextPage = $this->paginate->next;
        }
        if ($this->currentPage <= 0)
        {
            $this->currentPage = 1;
        }
    }

    public function setConfig($config)
    {
        if (count($config) > 0)
        {
            if (isset($config['builder']))
            {
                if ($config['builder'] instanceof \Phalcon\Mvc\Model\Criteria)
                {
                    $this->builder = $config['builder'] = (new \Phalcon\Mvc\Model\Query\Builder($config['builder']->getParams()))->from($config['builder']->getModelName());
                } else
                {
                    $this->builder = $config['builder'];
                }
            }
            // check isFrontPagination config
            if (isset($config['isFrontPagination']))
            {
                $this->isFrontPagination = $config['isFrontPagination'];
            }
            if ($this->isFrontPagination)
            {
                $this->isFirstLastPagi = false;
                $this->prevPageLabel = '<i class="fa fa-caret-left"></i>Previous';
                $this->nextPageLabel = 'Next<i class="fa fa-caret-right" aria-hidden="true"></i>';
            }

            if (isset($config['data']))
            {
                $this->data = & $config['data'];
            }
            if (isset($config['count']))
            {
                $this->totalItems = (int) $config['count'];
            }
            if (isset($config['limit']))
            {
                $this->limit = $config['limit'];
            }
            if (isset($config['page']))
            {
                $this->currentPage = $config['page'];
            }
            if (isset($config['maxPages']))
            {
                $this->maxPages = $config['maxPages'];
            }
            if (isset($config['nextPageLabel']))
            {
                $this->nextPageLabel = $config['nextPageLabel'];
            }
            if (isset($config['prevPageLabel']))
            {
                $this->prevPageLabel = $config['prevPageLabel'];
            }
            if (isset($config['firstPageLabel']))
            {
                $this->firstPageLabel = $config['firstPageLabel'];
            }
            if (isset($config['lastPageLabel']))
            {
                $this->lastPageLabel = $config['lastPageLabel'];
            }
            if (isset($config['numResultsTemplate']))
            {
                $this->numResultsTemplate = $config['numResultsTemplate'];
            }
            if (isset($config['perPageTemplate']))
            {
                $this->perPageTemplate = $config['perPageTemplate'];
            }
            if (isset($config['perPageOptions']))
            {
                $this->perPageOptions = $config['perPageOptions'];
            }
            if (isset($config['buttonLinkClass']))
            {
                $this->buttonLinkClass = $config['buttonLinkClass'];
            }
            if (isset($config['maxButtonCount']))
            {
                $this->maxButtonCount = $config['maxButtonCount'];
            }
            if (isset($config['pageQuery']))
            {
                $this->pageQuery = $config['pageQuery'];
            }
            if (isset($config['pageNumberDropdown']))
            {
                $this->pageNumberDropdown = $config['pageNumberDropdown'];
            }
        }
    }

    /**
     * Abstract Method
     */
    abstract protected function initialize();

    /**
     * Getting total item count
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * Getting total page count
     * @return int
     */
    public function getTotalPages()
    {
        return ($this->maxPages && $this->totalPages > $this->maxPages ? $this->maxPages : $this->totalPages);
    }

    /**
     * Getting current page count
     * @return int
     */
    public function getCurrentPage()
    {
        $pageCount = $this->getTotalPages();
        if ($this->currentPage >= $pageCount)
        {
            $this->currentPage = $pageCount;
        }
        return $this->currentPage;
    }

    /**
     * Getting current page count
     * @return int
     */
    public function getPreviousPage()
    {
        return $this->previousPage;
    }

    /**
     * Getting current page count
     * @return int
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    /**
     * Getting paginate items
     * @return array of object
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Getting limit of items per page
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Getting paginator navigation links
     * @param array $config Pagination configuration
     * @param boolean $echo true: echo html, false: return html
     * @return html
     */
    public function getLinks($config = NULL, $echo = true)
    {
        if ($config && is_array($config))
            $this->setConfig($config);
        $buttons = $this->createPageButtons();
        if (empty($buttons))
        {
            return;
        }
        $html = '<ul class="pagination justify-content-center">';
        $html .= implode("\n", $buttons);
        $html .= '</ul>';
        if ($echo)
        {
            echo $html;
        } else
        {
            return $html;
        }
    }

    /**
     * Creating pagination links
     * @return html
     */
    protected function createPageButtons()
    {
        if ($this->getTotalItems() <= $this->getLimit())
        {
            return array();
        }
        $pageCount = $this->getTotalPages();
        list($beginPage, $endPage) = $this->getPageRange();
        $currentPage = $this->getCurrentPage();
        $buttons = array();
        if ($this->isFirstLastPagi)
        {
            $buttons[] = $this->createPageButton($this->firstPageLabel, 1, 'extra_label', $currentPage <= 1, false);
        }
        if (($page = $currentPage - 1) < 0)
        {
            $page = 1;
        }
        $buttons[] = $this->createPageButton($this->prevPageLabel, $page, 'extra_label', $currentPage <= 1, false);
        if ($this->getTotalPages() <= $this->maxButtonCount)
        {
            for ($i = 1; $i <= $this->getTotalPages(); ++$i)
            {
                $buttons[] = $this->createPageButton($i, $i, '', false, ($i) == $currentPage);
            }
        } else
        {
            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int) floor(($this->maxButtonCount - 3) / 2);

            if ($currentPage + $numAdjacents > $this->getTotalPages())
            {
                $slidingStart = $this->getTotalPages() - $this->maxButtonCount + 2;
            } else
            {
                $slidingStart = $currentPage - $numAdjacents;
            }
            if ($slidingStart < 2)
                $slidingStart = 2;

            $slidingEnd = $slidingStart + $this->maxButtonCount - 3;
            if ($slidingEnd >= $this->getTotalPages())
                $slidingEnd = $this->getTotalPages() - 1;

            // Build the list of pages.
            $buttons[] = $this->createPageButton(1, 1, '', false, (1 == $currentPage));

            if ($slidingStart > 2)
            {
                $buttons[] = $this->createPageEllipsis();
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++)
            {
                $buttons[] = $this->createPageButton($i, $i, '', false, ($i) == $currentPage);
            }
            if ($slidingEnd < $this->getTotalPages() - 1)
            {
                $buttons[] = $this->createPageEllipsis();
            }
            $buttons[] = $this->createPageButton($this->getTotalPages(), $this->getTotalPages(), '', false, $currentPage == $this->getTotalPages());
        }
        if (($page = $currentPage + 1) >= $pageCount)
        {
            $page = $pageCount;
        }
        if ($this->pageNumberDropdown)
        {
            $buttons[] = $this->createPageDropdown();
        }
        $buttons[] = $this->createPageButton($this->nextPageLabel, $page, 'extra_label', $currentPage >= $pageCount, false);
        if ($this->isFirstLastPagi)
        {
            $buttons[] = $this->createPageButton($this->lastPageLabel, $pageCount, 'extra_label', $currentPage >= $pageCount, false);
        }
        return $buttons;
    }

    /**
     * Generate single pagination link
     * @param string $label
     * @param int $page
     * @param string $class
     * @param bool $hidden
     * @param bool $selected
     * @return html
     */
    protected function createPageButton($label, $page, $class = '', $hidden = '', $selected = '', $linkClass = null)
    {
        if ($hidden)
        {
            $class .= ' disabled';
        }
        if ($selected)
        {
            $class .= ' active';
        }
        $linkClass = isset($linkClass) ? $linkClass : $this->buttonLinkClass;

        return '<li data-page="' . $page . '" class="page-item ' . $class . '">' . ($this->tag->linkTo(array( ($hidden ? 'javascript:void(0);' : $this->createPageUrl($page)), $label, 'class' => $linkClass, 'data-page' => $page))) . '</li>';
    }

    protected function createPageEllipsis()
    {
        return '<li class="disabled page_ecllipsis">...</li>';
    }

    /**
     * Generate pagination link dropdown
     * @return html
     */
    protected function createPageDropdown()
    {

        $pageCount = $this->getTotalPages();
        $currentPage = $this->getCurrentPage();
        $selectHtml = "";
        $linkClass = $this->buttonLinkClass;
        for ($i = 1; $i <= $pageCount; $i++)
        {
            $selected = $currentPage == $i ? 'selected="selected"' : '';
            $selectHtml .= '<option ' . $selected . ' value="' . $i . '">' . $i . '</option>';
        }
        $allPaginationLinkli = '<li data-page="' . $currentPage . '" class="dyn_pagination_li" style="display:none;">' . $this->tag->linkTo(array($this->createPageUrl('{dyn_page}'), "", 'class' => $linkClass, 'data-page' => $currentPage)) . '</li>';
        return $allPaginationLinkli . '<li class="pagination_dropdown"><div class="form_wrap">
                    <div class="select select_box f_dropdown">
                        <div class="label-wrap">
                            <span class="arrow"></span>
                        <select id="pagination_page" class="f_dropdown_value">
                            ' . $selectHtml . '
                        </select>
                    </div>
                    <!-- select_box : end -->
                </div></li>
                <script type="text/javascript">
                    $(document).ready(function(e){
                        $(document).on("change",".pagination #pagination_page",function(e){
                            var page_num = $(this).val();
                            $(".dyn_pagination_li").data("page",page_num);
                            $(".dyn_pagination_li a").data("page",page_num);
                            var href = $(".dyn_pagination_li a").attr("href");
                            $(".dyn_pagination_li a").attr("href",href.replace("{dyn_page}",page_num));
                            $(".pagination li.dyn_pagination_li a")[0].click();
                        });
                    });
                </script>
                ';
    }

    /**
     * Getting page range to show
     * That means start paginator link and end paginator link
     * @return array
     */
    protected function getPageRange()
    {
        $currentPage = $this->getCurrentPage();
        $pageCount = $this->getTotalPages();
        $beginPage = max(1, $currentPage - (int) ($this->maxButtonCount / 2));
        if (($endPage = ($beginPage + $this->maxButtonCount) - 1) >= $pageCount)
        {
            $endPage = $pageCount;
            $beginPage = max(1, ($endPage - $this->maxButtonCount) + 1);
        }
        return array($beginPage, $endPage);
    }

    /**
     * Creating pagination link url
     * @param int $page
     * @return html
     */
    protected function createPageUrl($page)
    {
        $pageUrl = '';
        if (empty($this->urlPattern))
        {
            $rewriteUri = $this->router->getRewriteUri();
            $controller = $this->dispatcher->getControllerName();
            $action = $this->dispatcher->getActionName();
            $pageUrl .= substr($rewriteUri, 0, strpos($rewriteUri, $controller));
            $pageUrl .= $controller . '/';
            $pageUrl .= $action . '/';
            $pageUrl .= $page;
        } else
        {
            $pageUrl = $this->urlPattern;
            $pageUrl = str_replace('{page}', $page, $pageUrl);
            $pageUrl = str_replace('%7Bpage%7D', $page, $pageUrl);  // replace the encoded version also
        }

        if ($this->pageQuery !== false)
            $this->attachQuery($_GET);
        if (!empty($this->pageQuery))
        {
            $pageUrl .= (strstr($pageUrl, '?') ? '&' : '?') . $this->pageQuery;
        }
        return $pageUrl;
    }

    /**
     * Set page url pattern. This will be a custom route
     * @param string $pattern
     */
    public function setUriPattern($pattern)
    {
        $this->urlPattern = $pattern;
    }

    /**
     * Generate query string in the form or name value pair
     * from global $_GET array
     * @param array $params
     */
    protected function attachQuery($params)
    {
        if (isset($params['_url']))
        {
            unset($params['_url']);
        }
        $query = '';

        if (isset($this->ignoreParams))
            $ignoreParams = $this->ignoreParams;
        else
            $ignoreParams = [];

        if (count($params) > 0)
        {
            $newParams = [];
            foreach ($params as $key => $value)
            {
                if (/* !empty($key) && !empty($value) && */!isset($ignoreParams[$key]))
                {
                    $newParams[$key] = $value;
                    // $query .= $key . '=' . $value . '&';
                }
            }
            $query .= http_build_query($newParams);
        }
        // $query = ((substr($query, -1) == '&') ? substr($query, 0, -1) : $query);
        if ($query)
        {
            $this->pageQuery = $query;
        }
    }

    public function setIgnoreParams(array $arr)
    {
        $this->ignoreParams = array_combine($arr, $arr);
    }

    /**
     * Getting paginator navigation links
     * @return html
     */
    public function getResultsLabelHTML($config = null) {
        if ($config && is_array($config)) $this->setConfig($config);
        
        $template = (isset($this->numResultsTemplate) && $this->numResultsTemplate ? 
                    $this->numResultsTemplate : 
                    '<span class="pagination-results-label">Results : Viewing {start} - {end} / {total}</span>');
        
        $start = max((($this->getLimit() * ($this->getCurrentPage() - 1)) + 1), 0);
        $end = min($this->getLimit() * $this->getCurrentPage(), $this->getTotalItems());

        $template = str_ireplace('{start}', $start, $template);
        $template = str_ireplace('{end}', $end, $template);
        $template = str_ireplace('{total}', $this->getTotalItems(), $template);
        $template = str_ireplace('{page}', $this->getCurrentPage(), $template);
        $template = str_ireplace('{pages}', $this->getTotalPages(), $template);
        
        return $template;
    }

}
