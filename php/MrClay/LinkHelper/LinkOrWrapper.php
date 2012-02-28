<?php

/**
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_LinkHelper_LinkOrWrapper extends MrClay_LinkHelper {
    public $wrapperElement = 'strong';
    public $keepWrapperAttrs = array('id', 'class');

    /*
     * Render a complete anchor or, if href points to the current page, wrap
     * the content with an alternate element. In the case of the wrapper, only
     * the class and id attributes will be rendered.
     *
     * @param string $href anchor URL (not HTML escaped)
     *
     * @param string $innerHtml contents of element
     *
     * @param array $attrs non-href attributes for the anchor (values not HTML escaped)
     *
     * @param mixed $pointsHere does the href point to this page? (default =
     * null, _pointsHere() will be called to determine this)
     *
     * @return string HTML element
     */
    public function render($href, $innerHtml, $attrs = array(), $pointsHere = null)
    {
        if ($pointsHere === null) {
            $pointsHere = $this->pointsHere($href);
        }
        $el = 'a';
        $attrs['href'] = $href;
        if ($pointsHere) {
            // remove attrs that aren't in keepWrapperAttrs
            $wrapperAttrs = array();
            foreach ($this->keepWrapperAttrs as $name) {
                if (isset($attrs[$name])) {
                    $wrapperAttrs[$name] = $attrs[$name];
                }
            }
            $attrs = $wrapperAttrs;
            $el = $this->wrapperElement;
        }
        return $this->_openTag($el, $attrs) . "$innerHtml</$el>";
    }
}
