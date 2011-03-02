<?php

/**
 * Create wrapper P and BR elements in HTML depending on newlines. Useful when
 * users use newlines to signal line and paragraph breaks. In all cases output
 * should be well-formed markup.
 *
 * In DIV, LI, TD, and TH elements, Ps are only added when their would be at
 * least two of them.
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 *
 */
class MrClay_AutoP {

    public $encoding = 'UTF-8';

    /**
     * @var DOMDocument
     */
    protected $_doc = null;

    /**
     * @var DOMXPath
     */
    protected $_xpath = null;

    protected $_blocks = array(
        'table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li',
        '|pre|select|option|form|map|area|blockquote|math|style|p|h1|h2',
        '|h3|h4|h5|h6|hr|fieldset|legend|section|article|aside|hgroup|header|footer',
        '|nav|figure|figcaption|details|menu|summary'
    );

    /**
     * Descend into these elements to add Ps
     *
     * @var array
     */
    protected $_descendList = 'a|body|form|div|dd|dl|blockquote|td|th|li|ul|ol|section|article|aside|header|footer|details';

    /**
     * Add Ps inside these elements
     *
     * @var array
     */
    protected $_alterList = 'a|body|div|dd|blockquote|td|section|article|aside|header|footer|details';

    /**
     * In these elements, remove AUTOP if there's only one
     *
     * @var array
     */
    protected $_requireMultipleQuery = '//div | //td';

    protected $_unique = '';

    public function __construct()
    {
        $this->_blocks = explode('|', implode('', $this->_blocks));
        $this->_descendList = explode('|', $this->_descendList);
        $this->_alterList = explode('|', $this->_alterList);
        $this->_unique = md5(__FILE__);
    }

    /**
     * Create wrapper P and BR elements in HTML depending on newlines. Useful when
     * users use newlines to signal line and paragraph breaks. In all cases output
     * should be well-formed markup.
     *
     * In DIV, LI, TD, and TH elements, Ps are only added when their would be at
     * least two of them.
     *
     * @param string $html snippet
     * @return string|false output or false if parse error occurred
     */
    public function process($html)
    {
        // normalize whitespace
        $html = str_replace(array("\r\n", "\r"), "\n", $html);

        // allows preserving entities untouched
        $html = str_replace('&', $this->_unique . 'AMP', $html);

        // parse to DOM, suppressing loadHTML warnings http://www.php.net/manual/en/domdocument.loadhtml.php#95463
        $this->_doc = new DOMDocument();
       
        if (! $this->_parseBodyContent($html, $this->_doc, $this->encoding)) {
            return false;
        }

        $this->_xpath = new DOMXPath($this->_doc);
        // start processing recursively at the BODY element
        $nodeList = $this->_xpath->query('//body[1]');
        $this->_addParagraphs($nodeList->item(0));

        // serialize back to HTML
        $html = $this->_serializeBodyFragment($this->_doc);

        // split AUTOPs into multiples at /\n\n+/
        $html = preg_replace('/(' . $this->_unique . 'NL){2,}/', '</autop><autop>', $html);
        $html = str_replace(array($this->_unique . 'BR', $this->_unique . 'NL', '<br>'), '<br />', $html);
        $html = str_replace('<br /></autop>', '</autop>', $html);

        // re-parse so we can handle new AUTOP elements

        if (! $this->_parseBodyContent($html, $this->_doc, $this->encoding)) {
            return false;
        }
        // must re-create XPath object after DOM load
        $this->_xpath = new DOMXPath($this->_doc);

        // strip AUTOPs that only have comments/whitespace
        foreach ($this->_xpath->query('//autop') as $autop) {
            $hasContent = false;
            if (trim($autop->textContent) !== '') {
                $hasContent = true;
            } else {
                foreach ($autop->childNodes as $node) {
                    if ($node->nodeType === XML_ELEMENT_NODE) {
                        $hasContent = true;
                        break;
                    }
                }
            }
            if (! $hasContent) {
                // it's "empty", but move children out before deleting
                $parent = $autop->parentNode;
                while (null !== ($node = $autop->firstChild)) {
                    $parent->insertBefore($node, $autop);
                }
                $parent->removeChild($autop);
            }
        }

        // remove a single AUTOP inside certain elements
        foreach ($this->_xpath->query($this->_requireMultipleQuery) as $el) {
            $autops = $this->_xpath->query('./autop', $el);
            if ($autops->length === 1) {
                // only on AUTOP inside, move children out and delete it
                $autop = $autops->item(0);
                while (null !== ($node = $autop->firstChild)) {
                    $el->insertBefore($node, $autop);
                }
                $el->removeChild($autop);
            }
        }

        $html = $this->_serializeBodyFragment($this->_doc);

        // commit to converting AUTOPs to Ps
        $html = str_replace('<autop>', "\n<p>", $html);
        $html = str_replace('</autop>', "</p>\n", $html);
        
        $html = str_replace('<br>', '<br />', $html);
        $html = str_replace($this->_unique . 'AMP', '&', $html);
        return $html;
    }

    /**
     * Should the element be treated as block-level? For A elements, it depends
     * if the element contains other block-levels (HTML5)
     * 
     * @param DOMNode $node
     * @return bool
     */
    protected function _isBlock(DOMNode $node)
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return false;
        }
        $name = strtolower($node->nodeName);
        if ($name === 'a') {
            // (sigh) must check for block level descendants, thanks HTML5
            $containsBlock = false;
            foreach ($node->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE
                    && in_array(strtolower($child->nodeName), $this->_blocks)) {
                    return true;
                }
            }
            return false;
        } else {
            return in_array($name, $this->_blocks);
        }
    }

    /**
     * Add P and BR elements as necessary
     *
     * @param DOMElement $el
     */
    protected function _addParagraphs(DOMElement $el)
    {
        // if true, we can alter all child nodes, if not, we'll just call
        // _addParagraphs on each element in the descendInto list
        $alterInline = in_array(strtolower($el->nodeName), $this->_alterList);

        // inside affected elements, we want to trim leading whitespace from
        // the first text node
        $ltrimFirstTextNode = true;

        // should we open a new AUTOP element to move inline elements into?
        $openP = true;
        $autop = null;

        // after BR, ignore a newline
        $isFollowingBr = false;

        $node = $el->firstChild;
        while (null !== $node) {
            if ($alterInline) {
                if ($openP) {
                    $openP = false;
                    // create a P to move inline content into (this may be removed later)
                    $autop = $el->insertBefore($this->_doc->createElement('autop'), $node);
                }
            }

            $isElement = ($node->nodeType === XML_ELEMENT_NODE);
            $isBlock = $isElement && $this->_isBlock($node);

            if ($alterInline) {
                $isInline = $isElement && ! $isBlock;
                $isText = ($node->nodeType === XML_TEXT_NODE);
                $isLastInline = (! $node->nextSibling
                               || $this->_isBlock($node->nextSibling));

                if ($isElement) {
                    $isFollowingBr = (strtolower($node->nodeName) === 'br');
                }

                if ($isText) {
                    $nodeText = $node->nodeValue;
                    if ($ltrimFirstTextNode) {
                        $nodeText = ltrim($nodeText);
                        $ltrimFirstTextNode = false;
                    }
                    if ($isFollowingBr && preg_match('@^[ \\t]*\\n[ \\t]*@', $nodeText, $m)) {
                        // if a user ends a line with <br>, don't add a second BR
                        $nodeText = substr($nodeText, strlen($m[0]));
                    }
                    if ($isLastInline) {
                        $nodeText = rtrim($nodeText);
                    }
                    $nodeText = str_replace("\n", $this->_unique . 'NL', $nodeText);
                    $autop->appendChild($this->_doc->createTextNode($nodeText));
                    $tmpNode = $node;
                    $node = $node->nextSibling;
                    $el->removeChild($tmpNode);
                    continue;
                }
            }
            if ($isBlock || ! $node->nextSibling) {
                if ($isBlock) {
                    $nodeName = strtolower($node->nodeName);
                    if (in_array($nodeName, $this->_descendList)) {
                        $this->_addParagraphs($node);
                    }
                }
                $openP = true;
                $ltrimFirstTextNode = true;
            }
            if ($alterInline) {
                if (! $isBlock) {
                    $tmpNode = $node;
                    if ($isElement) {
                        $this->_handleInline($tmpNode);
                    }
                    $node = $node->nextSibling;
                    $autop->appendChild($tmpNode);
                    continue;
                }
            }

            $node = $node->nextSibling;
        }
    }

    /**
     * Turn newlines in inline elements into BRs
     *
     * @param DOMElement $el
     */
    protected function _handleInline(DOMElement $el)
    {
        $ignoreLeadingNewline = false;
        foreach ($el->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                if ($node->nodeValue === 'BR') {
                    $ignoreLeadingNewline = true;
                } else {
                    $ignoreLeadingNewline = false;
                    $this->_handleInline($node);
                }
                continue;
            }
            if ($node->nodeType === XML_TEXT_NODE) {
                $text = $node->nodeValue;
                if ($text[0] === "\n" && $ignoreLeadingNewline) {
                    $text = substr($text, 1);
                    $ignoreLeadingNewline = false;
                }
                $node->nodeValue = str_replace("\n", $this->_unique . 'BR', $text);
            }
        }
    }

    /**
     * Parse HTML into a DOMDocument
     * @param string $html
     * @param DOMDocument $doc
     * @param string $encoding
     * @return bool success
     */
    protected function _parseBodyContent($html, DOMDocument $doc, $encoding = 'UTF-8')
    {
        // parse to DOM, suppressing loadHTML warnings http://www.php.net/manual/en/domdocument.loadhtml.php#95463
        libxml_use_internal_errors(true);
        if (! @$doc->loadHTML("<html><meta http-equiv='content-type' content='text/html; charset={$encoding}'><body>"
                . $html . '</body></html>')) {
            return false;
        }
        return true;
    }

    /**
     * Serialize HTML from a DOMDocument
     * @param DOMDocument $doc
     * @return string
     */
    protected function _serializeBodyFragment(DOMDocument $doc)
    {
        $html = $doc->saveHTML();
        list(,$html) = explode('<body>', $html, 2);
        list($html) = explode('</body>', $html, 2);
        return $html;
    }
}
