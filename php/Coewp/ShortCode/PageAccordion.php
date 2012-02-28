<?php
/*
 *

SETUP

0. Setup Coewp_JsonApi so that whatever content you want to include can be fetched as 
   JSON when "?asJson" is appended to the URL.
1. Include the files in Accordion/public in your theme
2. Setup class autoloading in your theme/functions.php
2. include this code in theme/functions.php

function COE_shortcodeProxy_page_accordion($atts, $content = null) {
    Coewp_ShortCode_PageAccordion::$validHostnamePattern 
                = '/^' . preg_quote($_SERVER['SERVER_NAME']) . '$/';
    return Coewp_ShortCode_PageAccordion::handle($atts, $content);
}
add_shortcode("page_accordion", 'COE_shortcodeProxy_page_accordion');

 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class Coewp_ShortCode_PageAccordion extends Coewp_ShortCode_Accordion {

    /**
     * The hostname of the URL included must match this pattern
     * @var string regexp pattern
     */
    public static $validHostnamePattern = '/^$/';

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
            'id' => null,
            'name' => null,
            'url' => null,
            'title' => null,
            'expand' => '',
            'headingid' => '',
        ), $atts));

        $this->isExpanded = (bool) $expand;
        $this->titleHtml = '(could not load content)';
        
        if ($headingid) {
            $this->titleH3Attrs = "id='" . htmlspecialchars($headingid, ENT_QUOTES, 'UTF-8') . "'";
        }
        if ($url) {
            $data = $this->_fetch($url);
            if ($data) {
                $this->titleHtml = $data->title;
                $this->contentHtml = $data->content;
            }
        } else {
            $page = null;
            if ($id) {
                $theQuery = new WP_Query(array(
                    'post_type' => 'page',
                    'page_id' => $id,
                ));
                if ( count($theQuery->posts) ) {
                    $page = $theQuery->posts[0];
                }
            }
            elseif ( $name ) {
                $page = get_page_by_path($name);
                if (! $page) {
                    $url = site_url() . "/$name/";
                    $data = $$this->_fetch($url);
                    if ($data) {
                        $this->titleHtml = $data->title;
                        $this->contentHtml = $data->content;
                    }
                }
            }
            if ( $page ) {
                $this->contentHtml = apply_filters('the_content', $page->post_content);
                $this->contentHtml = str_replace(']]>', ']]&gt;', $this->contentHtml);
                $this->titleHtml = apply_filters('wp_title', $page->post_title);
            }
        }
        if ($this->titleHtml) {
            $this->titleHtml = apply_filters('wp_title', $this->titleHtml);
        }
    }

    /**
     * Fetch an object from a JSON URL on our domain(s)
     * @param string $url
     * @return stdObject|null
     */
    protected function _fetch($url)
    {
        $thisPath = parse_url('http://example.com' . $_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $components = parse_url($url);
        if ($components
            && $components['scheme'] === 'http'
            && preg_match(self::$validHostnamePattern, $components['host'])
            && $components['path'] !== $thisPath)
        {
            $separator = empty($components['query']) ? '?' : '&';
            $json = file_get_contents("{$url}{$separator}asJson");
            if ($json) {
                return json_decode($json);
            }
        }
        return false;
    }
}