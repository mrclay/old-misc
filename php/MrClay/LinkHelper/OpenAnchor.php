<?php

class MrClay_LinkHelper_OpenAnchor extends MrClay_LinkHelper {
    public $currentClass = 'current';

    /*
     * Render an opening anchor tag. If href points to the current page,
     * a special class will be added.
     *
     * @param string $href anchor URL (not HTML escaped)
     *
     * @param array $attrs non-href attributes for the anchor (values not HTML escaped)
     *
     * @param mixed $pointsHere does the href point to this page? (default =
     * null, pointsHere() will be called to determine this)
     *
     * @return string HTML open anchor tag
     */
    public function render($href, $attrs = array(), $pointsHere = null)
    {
        if ($pointsHere === null) {
            $pointsHere = $this->pointsHere($href);
        }
        if ($pointsHere) {
            $attrs['class'] = isset($attrs['class'])
                ? trim("{$attrs['class']} {$this->currentClass}")
                : $this->currentClass;
        }
        $attrs['href'] = $href;
        return $this->_openTag('a', $attrs);
    }
}
