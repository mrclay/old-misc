<?php

/**
 * Render HTML for a "mega menu"
 *
 * Recursively builds menu (does not check acl/resource/visibility). Some
 * features are controlled by addition options on the page objects:
 *
 * 'labelIsHtml' (bool) render label without escaping it
 * 'liAttrs' (array) attributes applied to the page's LI wrapper
 * 'ulAttrs' (array) attributes applied to the page's submenu UL
 * 'beforeUl' (string) content included before the submenu UL
 * 'afterUl' (string) content included after the submenu UL
 *
 * Usage:
 * <code>
 * $menu = new Zend_Navigation(array( ... ));
 * if ($found = $menu->findBy('label', 'SomeLabel')) {
 *    $found->setActive();
 * }
 * $helper = new MrClay_ZendHelpers_Navigation_MegaMenu();
 * $helper->setView(new Zend_View()); // optional
 * echo $helper->render($menu);
 * </code>
 *
 * Tested with Zend Framework 1.10.8
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://opensource.org/licenses/bsd-license.php
 */
class MrClay_ZendHelpers_Navigation_MegaMenu
    extends Zend_View_Helper_Navigation_HelperAbstract
{
    /**
     * class applied to LI of the active page
     * @var string
     */
    public $activeClass = 'MM_active';

    /**
     * class applied to the top-level LI of the active branch
     * @var string
     */
    public $topActiveClass = 'MM_topActiveBranch';

    /**
     * class applied to LI above the LI of the active page
     * @var string
     */
    public $parentActiveClass = 'MM_activeParent';

    /**
     * if a non-empty string S exists for key I, a sublist at depth I will be
     * wrapped with a DIV with classname S.
     * @var array
     */
    public $listWrappersByDepth = array();

    /**
     * classnames for sublist elements at various depths.
     * @var array
     */
    public $listClassesByDepth = array('', 'sub', 'subsub');

    /**
     * attributes for the root-level UL
     * @var array
     */
    public $rootListAttrs = array('class' => 'MM');

    /**
     * deepest active page found, or null
     * @var Zend_Navigation_Page
     */
    protected $_foundPage;

    /**
     * depth of deepest active page found, or null
     * @var int
     */
    protected $_foundDepth;


    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               operate on
     * @return Zend_View_Helper_Navigation_Menu      fluent interface,
     *                                               returns self
     */
    public function menu(Zend_Navigation_Container $container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }
        return $this;
    }

    // Public methods:

    /**
     * Returns an HTML string containing an 'a' element for the given page if
     * the page's href is not empty, and a 'span' element if it is empty
     *
     * Overrides {@link Zend_View_Helper_Navigation_Abstract::htmlify()}.
     *
     * @param  Zend_Navigation_Page $page  page to generate HTML for
     * @return string                      HTML string for the given page
     */
    public function htmlify(Zend_Navigation_Page $page)
    {
        return $page->isActive()
           ? $this->_renderActivePage($page)
           : $this->_renderInactivePage($page);
    }


    // Render methods:

    /**
     * Renders menu
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::render()}.
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     */
    public function render(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }
        if (null === $this->view) {
            $this->setView(new Zend_View());
        }
        // find deepest active
        if ($found = $this->findActive($container)) {
            $this->_foundPage = $found['page'];
            $this->_foundDepth = $found['depth'];
        } else {
            $this->_foundPage = null;
            $this->_foundDepth = null;
        }

        $listElement = 'ul';
        $listAttrs = $this->_htmlAttribs($this->rootListAttrs);

        $html = "<$listElement$listAttrs>";

        unset($listAttrs, $found); // save a little mem while recursing

        foreach ($container->getPages() as $page) {
            $isActiveBranch = $page->isActive(true);
            $html .= $this->_renderPageAndSubmenu($page, $isActiveBranch, 0);
        }

        return "$html</$listElement>";
    }

    /**
     * render HTML for an LI and possibly a nested submenu
     * @param Zend_Navigation_Page $page
     * @param bool $isActiveBranch
     * @param int $depth
     * @return string
     */
    protected function _renderPageAndSubmenu(Zend_Navigation_Page $page, $isActiveBranch, $depth)
    {
        // render li and page
        if (! is_array($liAttrs = $page->get('liAttrs'))) {
            $liAttrs = array();
        }
        if ($isActiveBranch) {
            if ($page === $this->_foundPage) {
                $this->_appendClass($liAttrs, $this->activeClass);
            }
            if ($depth === 0) {
                $this->_appendClass($liAttrs, $this->topActiveClass);
            }
            if ($page->hasPage($this->_foundPage)) {
                $this->_appendClass($liAttrs, $this->parentActiveClass);
            }
        }
        $html = '<li' . $this->_htmlAttribs($liAttrs) . '>' . $this->htmlify($page);

        // render sublist
        unset($liAttrs); // save a little mem while recursing

        if ($subpages = $page->getPages()) {
            $html .= $this->_renderSubmenu($page, $subpages, $isActiveBranch, $depth);
        }

        return $html . '</li>';
    }

    /**
     * render HTML for a nested submenu
     * @param Zend_Navigation_Page $page
     * @param bool $isActiveBranch
     * @param int $depth
     * @return string
     */
    protected function _renderSubmenu(Zend_Navigation_Page $page, $subpages, $isActiveBranch, $depth)
    {
        $html = '';
        $beforeUl = $page->get('beforeUl');
        $afterUl = $page->get('afterUl');
        if (! empty($this->listWrappersByDepth[$depth + 1])) {
            $beforeUl = "<div class=\"{$this->listWrappersByDepth[$depth + 1]}\">" . $beforeUl;
            $afterUl .= '</div>';
        }

        // allow page to set list attributes
        if (! is_array($ulAttrs = $page->get('ulAttrs'))) {
            $ulAttrs = array();
        }
        if (! empty($this->listClassesByDepth[$depth + 1])) {
            $this->_appendClass($ulAttrs, $this->listClassesByDepth[$depth + 1]);
        }
        $html = $beforeUl . '<ul' . $this->_htmlAttribs($ulAttrs) . '>';

        unset($ulAttrs, $beforeUl); // save a little mem while recursing

        foreach ($subpages as $subpage) {
            $html .= $this->_renderPageAndSubmenu($subpage, $isActiveBranch, $depth + 1);
        }

        return $html . "</ul>" . $afterUl;
    }

    protected function _renderActivePage(Zend_Navigation_Page $page)
    {
        // same for now, but we could render active pages as STRONG elements, etc
        return $this->_renderInactivePage($page);
    }

    protected function _renderInactivePage(Zend_Navigation_Page $page)
    {
        // get label and title
        $label = $page->getLabel();
        $title = $page->getTitle();

        // get attribs for element
        $attribs = array(
            'id'     => $page->getId(),
            'title'  => $title,
            'class'  => $page->getClass()
        );

        // does page have a href?
        if ($href = $page->getHref()) {
            $element = 'a';
            $attribs['href'] = $href;
            $attribs['target'] = $page->getTarget();
        } else {
            $element = 'span';
        }

        if (! $page->get('labelIsHtml')) {
            $label = $this->view->escape($label);
        }

        return '<' . $element . $this->_htmlAttribs($attribs) . '>'
             . $label
             . '</' . $element . '>';
    }

    /**
     * Append to the class attribute of a set of attributes
     * @param array $attrs
     * @param string $class
     */
    protected function _appendClass(& $attrs, $class)
    {
        $attrs['class'] = empty($attrs['class'])
            ? $class
            : "{$attrs['class']} $class";
    }

}
