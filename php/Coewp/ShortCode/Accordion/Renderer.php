<?php

/**
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class Coewp_ShortCode_Accordion_Renderer {
    /**
     * @var Coewp_ShortCode_Accordion
     */
    public $accordion;

    public function __construct(Coewp_ShortCode_Accordion $accordion)
    {
        $this->accordion = $accordion;
    }

    public function render()
    {
        $content = $this->accordion->contentHtml;
        $title = $this->accordion->titleHtml;
        $h3attrs = $this->accordion->titleH3Attrs;

        $className = 'page-accordion';
        if ($this->accordion->isExpanded) {
            $className .= ' page-accordion-e';
        }

        return "<div class='$className'><h3 class='page-accordion-heading' $h3attrs>$title</h3>"
             . "<div class='page-accordion-content'>$content</div></div>";
    }
}