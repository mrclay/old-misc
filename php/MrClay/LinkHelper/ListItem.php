<?php

class MrClay_LinkHelper_ListItem extends MrClay_LinkHelper_LinkOrWrapper {
    public $liCurrentClass = 'current';

    /*
     * Render a List item wrapped around an anchor or, if href points to the
     * current page, an alternate element. In the case of the wrapper, only
     * the class and id attributes will be rendered.
     *
     * @param string $href anchor URL (not HTML escaped)
     *
     * @param string $innerHtml contents of anchor
     *
     * @param array $aAttrs non-href attributes for the anchor (values not HTML escaped)
     *
     * @param array $liAttrs attributes for the li (values not HTML escaped)
     *
     * @param mixed $pointsHere does the href point to this page? (default =
     * null, _pointsHere() will be called to determine this)
     *
     * @return string HTML element
     */
    public function render($href, $innerHtml, $aAttrs = array(), $liAttrs = array(), $pointsHere = null)
    {
        if ($pointsHere === null) {
            $pointsHere = $this->pointsHere($href);
        }
        // render a or wrapper, then wrap with li
        $innerHtml = parent::render($href, $innerHtml, $aAttrs, $pointsHere);
        if ($pointsHere) {
            $liAttrs['class'] = isset($liAttrs['class'])
                ? trim("{$liAttrs['class']} {$this->liCurrentClass}")
                : $this->liCurrentClass;
        }
        return $this->_openTag('li', $liAttrs) . "$innerHtml</li>";
    }
}
