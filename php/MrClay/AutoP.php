<?php

/**
 * Create wrapper P and BR elements in HTML depending on newlines. Good for
 * processing user content which replies on double newlines to signal a new
 * paragraph. In all cases output should be well-formed markup.
 *
 * In DIV, LI, TD, and TH elements, Ps are only added when their would be at
 * least two of them.
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 *
 */
class MrClay_AutoP {

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
     * Add Ps inside these elements
     *
     * @var array
     */
    protected $_descendInto = 'body|form|div|dd|dl|blockquote|td|th|li|ul|section|article|aside|header|footer|details';

    /**
     * In these elements, remove AUTOP if there's only one
     *
     * @var array
     */
    protected $_requireMultipleQuery = '//li | //div | //th | //td';

    protected $_tokenPrefix = '';

    public function __construct()
    {
        $this->_blocks = explode('|', implode('', $this->_blocks));
        $this->_descendInto = explode('|', $this->_descendInto);
        $this->_tokenPrefix = md5(microtime(true));
    }

    /**
     * Create wrapper P and BR elements in HTML depending on newlines. Good for
     * processing user content which replies on double newlines to signal a new
     * paragraph. In all cases output should be well-formed markup.
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

        // parse to DOM, suppressing loadHTML warnings http://www.php.net/manual/en/domdocument.loadhtml.php#95463
        $this->_doc = new DOMDocument();
        libxml_use_internal_errors(true);
        if (! @$this->_doc->loadHTML("<html><body>" . $html . '</body></html>')) {
            return false;
        }

        $this->_xpath = new DOMXPath($this->_doc);
        // start processing recursively at the BODY element
        $nodeList = $this->_xpath->query('//body[1]');
        $this->_addParagraphs($nodeList->item(0));

        // serialize back to HTML
        $html = $this->_doc->saveHTML();

        // split AUTOPs into multiples at /\n\n+/
        $html = preg_replace('/(' . $this->_tokenPrefix . 'NL){2,}/', '</autop><autop>', $html);
        $html = str_replace(array($this->_tokenPrefix . 'BR', $this->_tokenPrefix . 'NL', '<br>'), '<br />', $html);
        $html = str_replace('<br /></autop>', '</autop>', $html);

        // re-parse so we can handle new AUTOP elements

        if (! @$this->_doc->loadHTML($html)) {
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

        // serialize & finish up
        $html = $this->_doc->saveHTML();

        $html = str_replace('<autop>', "\n<p>", $html);
        $html = str_replace('</autop>', "</p>\n", $html);
        $html = str_replace('<br>', '<br />', $html);

        list(,$html) = explode('<body>', $html, 2);
        list($html) = explode('</body>', $html, 2);
        return $html;
    }

    /**
     * Add P and BR elements as necessary
     *
     * @param DOMElement $el
     */
    protected function _addParagraphs(DOMElement $el)
    {
        $inFirstWhitespace = true;
        $blocks = array();
        $openP = true;
        $p = null;
        $ignoreLeadingNewline = false;
        $node = $el->firstChild;
        while (null !== $node) {
            if ($openP) {
                $openP = false;
                // create a P to move inline content into (this may be removed later)
                $p = $el->insertBefore($this->_doc->createElement('autop'), $node);
            }

            $isElement = ($node->nodeType === XML_ELEMENT_NODE);
            $isBlock = $isElement && in_array(strtolower($node->nodeName), $this->_blocks);
            $isInline = $isElement && ! $isBlock;
            $isText = ($node->nodeType === XML_TEXT_NODE);
            $isLastInline = (! $node->nextSibling
                           || ($node->nextSibling->nodeType === XML_ELEMENT_NODE
                               && in_array(strtolower($node->nextSibling->nodeValue), $this->_blocks)
                           ));

            if ($isElement) {
                $ignoreLeadingNewline = ($node->nodeValue === 'BR');
            }

            if ($isText) {
                $nodeText = $node->nodeValue;
                if ($inFirstWhitespace) {
                    $nodeText = ltrim($nodeText);
                    $inFirstWhitespace = false;
                }
                if (substr($nodeText, 0, 1) === "\n" && $ignoreLeadingNewline) {
                    $nodeText = substr($nodeText, 1);
                }
                if ($isLastInline) {
                    $nodeText = rtrim($nodeText);
                }
                $nodeText = str_replace("\n", $this->_tokenPrefix . 'NL', $nodeText);
                $p->appendChild($this->_doc->createTextNode($nodeText));
                $curr = $node;
                $node = $node->nextSibling;
                $el->removeChild($curr);
                continue;
            }

            if ($isBlock || ! $node->nextSibling) {
                if ($isBlock) {
                    $nodeName = strtolower($node->nodeName);
                    if (in_array($nodeName, $this->_descendInto)) {
                        $this->_addParagraphs($node);
                    }
                }
                $openP = true;
                $inFirstWhitespace = true;
            }

            if (! $isBlock) {
                $curr = $node;
                if ($isElement) {
                    $this->_handleInline($curr);
                }
                $node = $node->nextSibling;
                $p->appendChild($curr);
                continue;
            }

            $node = $node->nextSibling;
        }
    }

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
                $node->nodeValue = str_replace("\n", $this->_tokenPrefix . 'BR', $text);
            }
        }
    }
}
