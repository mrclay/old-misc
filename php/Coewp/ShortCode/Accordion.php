<?php
/*

SETUP

1. Include the files in Accordion/public in your theme
2. Setup class autoloading in your theme/functions.php
2. include this code in theme/functions.php

function COE_shortcodeProxy_accordion($atts, $content = null) {
    return Coewp_ShortCode_Accordion::handle($atts, $content);
}
add_shortcode("accordion", 'COE_shortcodeProxy_accordion');

*/
class Coewp_ShortCode_Accordion {

    public $titleHtml = 'more...';
    public $titleH3Attrs = '';
    public $contentHtml = '';
    public $isExpanded = false;

    /**
     * Build and render the shortcode. For performance reasons, do not register this with
     * WordPress's shortcode. Instead register a proxy function that calls this.
     * 
     * @param array $atts
     * @param string $content
     * @param Coewp_ShortCode_Accordion_Renderer $renderer
     * @return string
     */
    public static function handle($atts, $content, Coewp_ShortCode_Accordion_Renderer $renderer = null)
    {
        $obj = new self();
        $obj->_configure($atts, $content);
        if (! $renderer) {
            $renderer = new Coewp_ShortCode_Accordion_Renderer($obj);
        }
        return $renderer->render();
    }

    /**
     * Configure the content of the accordion
     * @param array $atts
     * @param string $content
     */
    protected function _configure($atts, $content)
    {
        extract(shortcode_atts(array(
            'title' => '',
            'expand' => '',
        ), $atts));
        
        $this->contentHtml = $content;
        if (empty($title)) {
            $this->contentHtml = preg_replace_callback('@<(h[1-6])([^>]*)>(.*?)</\\1>@i', array($this, '_CB'), $this->contentHtml, 1);
        } else {
            $this->titleHtml = apply_filters('wp_title', $title);
        }
        $this->isExpanded = (bool) $expand;
    }

    protected function _CB($m) {
        $this->titleH3Attrs = $m[2];
        $this->titleHtml = $m[3];
        return "";
    }
}