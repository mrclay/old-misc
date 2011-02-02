<?php

/**
 * This implements a WordPress text filter to handle output of the wp_nav_menu
 * function (also used by the "Custom Menu" widget). Submenu ULs that don't have
 * the active menu item are removed, so the user has to drill down into the
 * submenus.
 *
 * Under the hood, DOMDocument/DOMXPath are used, so it requires the libxml
 * PHP extension: http://www.php.net/manual/en/dom.requirements.php
 *
 * <code>
 * // in functions.php
 * Coewp_MenuFilter::add();
 * </code>
 */
class Coewp_MenuFilter {

	/**
	 * Add this filter to WordPress's wp_nav_menu hook
	 */
	public static function add()
	{
		add_filter('wp_nav_menu', array('Coewp_MenuFilter', 'staticFilter'));
	}

	/**
     * Filter menu removing submenus in inactive branches. This static version
     * allows immediate disposal of the object after filtering
     *
     * @param $html
     * @return string
     */
    public static function staticFilter($html)
	{
		$filter = new self;
		return $filter->filter($html);
	}

	/**
     * Filter menu removing submenus in inactive branches
     *
     * @param $html
     * @return string
     */
    public function filter($html)
    {
        $doc = new DOMDocument();

        // supress loadHTML warnings http://www.php.net/manual/en/domdocument.loadhtml.php#95463
        libxml_use_internal_errors(true);

        if (! @$doc->loadHTML("<html><body>" . $html . '</body></html>')) {
            return $html;
        }
        $this->_xpath = new DOMXPath($doc);
        $menu = $this->_xpath->query('//ul[1]');
        if ($menu->length) {
            // recursively walk menu ULs
            $this->_handleUl($menu->item(0));
        }

        $html = $doc->saveHTML();
        list(,$html) = explode('<body>', $html, 2);
        list($html) = explode('</body>', $html, 2);

        return $html;
    }

    /**
     * Check all child LIs, and remove their submenus if they don't contain
     * an LI with class "current-menu-item". If it is the current branch,
     * call this function recursively on the submenu UL.
     *
     * @param DOMElement $ul
     */
    protected function _handleUl(DOMElement $ul)
    {
        foreach ($this->_xpath->query('./li', $ul) as $li) {
            $childUl = $this->_xpath->query('./ul', $li);
            if ($childUl->length) {
                $isActiveBranch = false;
                foreach ($this->_xpath->query('. | .//li', $li) as $descLi) {
                    if (preg_match('@(^| )current-menu-item( |$)@', $descLi->getAttribute('class'))) {
                        $isActiveBranch = true;
                        break;
                    }
                }
                if ($isActiveBranch) {
                    $this->_handleUl($childUl->item(0));
                } else {
                    $li->removeChild($childUl->item(0));
                }
            }
        }
    }

    /**
     * @var DOMXPath
     */
    protected $_xpath = null;
}