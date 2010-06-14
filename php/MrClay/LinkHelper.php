<?php

/*
 * Base class for rendering HTML navigational elements that change depending on
 * the current requested page. Only detects the current page on root-relative
 * URIs or full URLs, not path-relative URIs.
 *
 * @author Steve Clay
 * @link http://mrclay.org/
 * @abstract
 */
abstract class MrClay_LinkHelper {
    protected $_thisUri;
    protected $_ignoreQueryString;

    /*
     * @param bool $ignoreQueryString if set to true, /foo?1 and /foo?2 will be
     * considered the same URI
     *
     * @param string $thisUri if REQUEST_URI is unreliable, you'll have to
     * manually provide the current request URI
     */
    public function __construct($ignoreQueryString = false, $thisUri = '')
    {
        $this->_ignoreQueryString = $ignoreQueryString;
        if (empty($thisUri)) {
            // @todo IIS fix
            $thisUri = $_SERVER['REQUEST_URI'];
        }
        $this->_thisUri = $thisUri;
    }

    /*
     * Does the give URL point to the URL the user requested?
     *
     * @param string $url a full URL or a root-relative path.
     * (Cannot be a file-relative path)
     *
     * @return bool
     */
    public function pointsHere($url)
    {
        $thisUri = $this->_thisUri;
        if ($this->_ignoreQueryString) {
            list($url) = explode('?', $url, 2);
            list($thisUri) = explode('?', $thisUri, 2);
        }
        // @todo resolve path-relative hrefs
        $urlParts = parse_url($url);
        if (isset($urlParts['host']) && ($urlParts['host'] !== $_SERVER['HTTP_HOST'])) {
            return false;
        }
        $uri = (! $this->_ignoreQueryString && isset($urlParts['query']))
            ? "{$urlParts['path']}?{$urlParts['query']}"
            : $urlParts['path'];
        return ($uri === $thisUri);
    }

    /*
     * Render an open HTML tag
     *
     * @param string $tagName
     *
     * @param array $attrs attributes (values not HTML escaped)
     *
     * @return string open element tag
     */
    protected function _openTag($tagName, $attrs = array()) {
        if (! $attrs) {
            return "<$tagName>";
        }
        foreach ($attrs as $attr => $val) {
            $flattenedAttrs[] = "$attr=\"" . $this->_h($val) . "\"";
        }
        return "<$tagName " . implode(' ', $flattenedAttrs) . ">";
    }

    /*
     * Escape attribute values for HTML
     *
     * @param string $txt
     *
     * @return string
     */
    protected function _h($txt)
    {
        return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
    }
}
