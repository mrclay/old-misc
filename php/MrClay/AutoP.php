<?php

/**
 * Create wrapper P and BR elements in HTML depending on newlines. Useful when
 * users use newlines to signal line and paragraph breaks. In all cases output
 * should be well-formed markup.
 *
 * In DIV elements, Ps are only added when their would be at
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
    protected $_descendList = 'body|form|div|blockquote|section|article|aside|header|footer|details';

    /**
     * Add Ps inside these elements
     *
     * @var array
     */
    protected $_alterList = 'body|div|blockquote|section|article|aside|header|footer|details';

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

        $this->_doc = new DOMDocument();
       
        // parse to DOM, suppressing loadHTML warnings http://www.php.net/manual/en/domdocument.loadhtml.php#95463
        libxml_use_internal_errors(true);
        if (! @$this->_doc->loadHTML("<html><meta http-equiv='content-type' content='text/html; charset={$this->encoding}'><body>"
                . $html . '</body></html>')) {
            return false;
        }

        $this->_xpath = new DOMXPath($this->_doc);
        // start processing recursively at the BODY element
        $nodeList = $this->_xpath->query('//body[1]');
        $this->_addParagraphs($nodeList->item(0));

        // serialize back to HTML
        $html = $this->_doc->saveHTML();

        // split AUTOPs into multiples at /\n\n+/
        $html = preg_replace('/(' . $this->_unique . 'NL){2,}/', '</autop><autop>', $html);
        $html = str_replace(array($this->_unique . 'BR', $this->_unique . 'NL', '<br>'), '<br />', $html);
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
                // strip w/ preg_replace later (faster than moving nodes out)
                $autop->setAttribute("r", "1");
            }
        }

        // remove a single AUTOP inside certain elements
        
        foreach ($this->_xpath->query('//div') as $el) {
            $autops = $this->_xpath->query('./autop', $el);
            if ($autops->length === 1) {
                // strip w/ preg_replace later (faster than moving nodes out)
                $autops->item(0)->setAttribute("r", "1");
            }
        }
        
        $html = $this->_doc->saveHTML();
        
        // trim to the contents of BODY
        $bodyStart = strpos($html, '<body>');
        $bodyEnd = strpos($html, '</body>', $bodyStart + 6);
        $html = substr($html, $bodyStart + 6, $bodyEnd - $bodyStart - 6);
        
        // strip AUTOPs that should be removed
        $html = preg_replace('@<autop r="1">(.*?)</autop>@', '\\1', $html);

        // commit to converting AUTOPs to Ps
        $html = str_replace('<autop>', "\n<p>", $html);
        $html = str_replace('</autop>', "</p>\n", $html);
        
        $html = str_replace('<br>', '<br />', $html);
        $html = str_replace($this->_unique . 'AMP', '&', $html);
        return $html;
    }

    /**
     * Add P and BR elements as necessary
     *
     * @param DOMElement $el
     */
    protected function _addParagraphs(DOMElement $el)
    {
        // no need to recurse, just queue up
        $elsToProcess = array($el);
        $inlinesToProcess = array();
        while ($el = array_shift($elsToProcess)) {
            // if true, we can alter all child nodes, if not, we'll just call
            // _addParagraphs on each element in the descendInto list
            $alterInline = in_array($el->nodeName, $this->_alterList);

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
                if ($isElement) {
                    $elName = $node->nodeName;
                }
                $isBlock = ($isElement && in_array($elName, $this->_blocks));

                if ($alterInline) {
                    $isInline = $isElement && ! $isBlock;
                    $isText = ($node->nodeType === XML_TEXT_NODE);
                    $isLastInline = (! $node->nextSibling
                                   || ($node->nextSibling->nodeType === XML_ELEMENT_NODE
                                       && in_array($node->nextSibling->nodeName, $this->_blocks)));
                    if ($isElement) {
                        $isFollowingBr = ($node->nodeName === 'br');
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
                        $tmpNode = $node;
                        $node = $node->nextSibling; // move loop to next node

                        // alter node in place, then move into AUTOP
                        $tmpNode->nodeValue = $nodeText;
                        $autop->appendChild($tmpNode);

                        continue;
                    }
                }
                if ($isBlock || ! $node->nextSibling) {
                    if ($isBlock) {
                        if (in_array($node->nodeName, $this->_descendList)) {
                            $elsToProcess[] = $node;
                            //$this->_addParagraphs($node);
                        }
                    }
                    $openP = true;
                    $ltrimFirstTextNode = true;
                }
                if ($alterInline) {
                    if (! $isBlock) {
                        $tmpNode = $node;
                        if ($isElement && false !== strpos($tmpNode->textContent, "\n")) {
                            $inlinesToProcess[] = $tmpNode;
                        }
                        $node = $node->nextSibling;
                        $autop->appendChild($tmpNode);
                        continue;
                    }
                }

                $node = $node->nextSibling;
            }
        }

        // handle inline nodes
        // no need to recurse, just queue up
        while ($el = array_shift($inlinesToProcess)) {
            $ignoreLeadingNewline = false;
            foreach ($el->childNodes as $node) {
                if ($node->nodeType === XML_ELEMENT_NODE) {
                    if ($node->nodeValue === 'BR') {
                        $ignoreLeadingNewline = true;
                    } else {
                        $ignoreLeadingNewline = false;
                        if (false !== strpos($node->textContent, "\n")) {
                            $inlinesToProcess[] = $node;
                        }
                    }
                    continue;
                } elseif ($node->nodeType === XML_TEXT_NODE) {
                    $text = $node->nodeValue;
                    if ($text[0] === "\n" && $ignoreLeadingNewline) {
                        $text = substr($text, 1);
                        $ignoreLeadingNewline = false;
                    }
                    $node->nodeValue = str_replace("\n", $this->_unique . 'BR', $text);
                }
            }
        }
    }
}
