<?php
// 2006-01-08 Created on PHP4 w/o DOM extensions :(
// 2008-10-12 PHP5 touch up (no notices). Published for your derision :)

define('HTML_TRANSITIONAL', "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\n\"http://www.w3.org/TR/html4/loose.dtd\">\n");
define('DEFAULT_DOCTYPE', HTML_TRANSITIONAL);
define('HTML_MINIMIZED_ELEMENTS', '|img|col|input|link|hr|');

class _Node {
	var $childNodes = array();
	var $ownerDocument;
	function _Node($ownerDocument) {
		$this->ownerDocument =& $ownerDocument;
	}
	function appendChild($newChild) {
		$this->childNodes[] =& $newChild;
		return $newChild;
	}
	function getChildNodes() {return $this->childNodes;}
	
	function getInnerHtml() {
		$buffer = '';
		$i = 0;
		while ($child =& $this->_getChild($i++)) {
			$buffer .= $child->getOuterHtml();
		}
		return $buffer;
	}
	function getOuterHtml() {return $this->getInnerHtml();}
	
	function _getChild($i) {
		return (isset($this->childNodes[$i]))? $this->childNodes[$i] : NULL;
	}
}

class _Element extends _Node {
	var $tagName;
	var $attributes = array();
	var $_minimized;
	var $_whiteSpaceData;
	function _Element($tagName, $ownerDocument) {
		$this->tagName = $tagName;
		$this->_minimized = (
			   $ownerDocument->_type == 'html'
			&& strpos(HTML_MINIMIZED_ELEMENTS, "|{$tagName}|")!==false
		);
		parent::_Node($ownerDocument);
	}
	function getOuterHtml() {
		$attributes = '';
		foreach ($this->attributes as $name => $value) {
			$attributes .= " {$name}=\"{$value}\"";
		}
		if ($this->_minimized) {
			$startTag = "<{$this->tagName}{$attributes}".$this->ownerDocument->_minimizedEndToken;
			$endTag = '';
		} else {
			$startTag = "<{$this->tagName}{$attributes}>";
			$endTag = "</{$this->tagName}>";
		}
		list($ws1,$ws2) = $this->ownerDocument->_whiteSpace->open($this);
		$innerHtml = $this->getInnerHtml();
		list($ws3,$ws4) = $this->ownerDocument->_whiteSpace->close($this);
		return ($this->_minimized)? "{$ws1}{$startTag}{$ws4}"
			: "{$ws1}{$startTag}{$ws2}{$innerHtml}{$ws3}{$endTag}{$ws4}";
	}
}

class _Text extends _Node {
	var $nodeValue;
	function _Text($nodeValue, &$ownerDocument) {
		$this->nodeValue = $nodeValue;
		parent::_Node($ownerDocument);
	}
	function getInnerHTML() {return $this->nodeValue;}
}

// don't call me, use an extension class
class _Document extends _Node {
	var $charset = 'ISO-8859-1';	// see http://php.net/htmlentities
	var $quoteStyle = ENT_QUOTES;	// see http://php.net/htmlentities
	var $_type;
	var $_minimizedEndToken;
	var $_whiteSpace;
	function _Document($type) {
		$this->_whiteSpace =& new WhiteSpace_None();
		$this->_type = $type;
		$this->ownerDocument = NULL;
		parent::_Node($this->ownerDocument); // I'm the owner
	}
	function createElement($tagName) {return new _Element($tagName, $this);}
	function createTextNode($text) {return new _Text($text, $this);}
	function setCharset($charset) {$this->charset = $charset;}
	function setQuoteStyle($quoteStyle) {$this->quoteStyle = $quoteStyle;}
	function setWhiteSpace(&$ws) {$this->_whiteSpace =& $ws;}
}

class HTMLDocument extends _Document {
	var $body;
	var $title;
	var $_doctype;
	function HTMLDocument($titleValue = 'Untitled', $doctype = DEFAULT_DOCTYPE) {
		$this->_doctype = $doctype;
		$this->_minimizedEndToken = '>';
		parent::_Document('html');
		$html =& $this->appendChild($this->createElement('html'));
		$head =& $html->appendChild($this->createElement('head'));
		$title =& $head->appendChild($this->createElement('title'));
		$titleText =& $title->appendChild($this->createTextNode($titleValue));
		$this->title =& $titleText->nodeValue;
		$this->body =& $html->appendChild($this->createElement('body'));
	}
	function toString() {return $this->_doctype.parent::getInnerHtml();}
}

class XMLDocument extends _Document {
	var $root;
	var $prolog = "<?xml version=\"1.0\"?>";
	var $declarations = array();
	function XMLDocument($rootTagName) {
		$this->_minimizedEndToken = ' />';
		parent::_Document('xml');
		$this->root =& $this->appendChild($this->createElement($rootTagName));
	}
	function addDeclaration($text) {
		$this->declarations[] = $text;
	}
	function toString() {
		$prolog = (!empty($this->prolog))? $this->prolog."\n" : '';
		$declarations = (count($this->declarations))? 
			join("\n",$this->declarations)."\n"	: '';
		$innerHTML = parent::getInnerHtml();
		return "{$prolog}{$declarations}{$innerHTML}";
	}
}

// abstract
class _WhiteSpaceManager {
	var $_indentLevel = 0;
	var $_i = '';
	var $_ws;
	function _WhiteSpaceManager($ws = "\t") {$this->_ws = $ws;}
	function open(&$el) {return array('','');}
	function close(&$el) {return array('','');}
	function _tagIn($tagName, $list) {
		return (strpos($list, strtolower("|{$tagName}|"))!== false);
	}
	function _changeIndent($delta) {
		$this->_indentLevel = max(0, $this->_indentLevel + $delta);
		$this->_i = str_repeat($this->_ws, $this->_indentLevel);
	}
}

// no whitespace
class WhiteSpace_None extends _WhiteSpaceManager {
}


define('WSHTML_BLOCK_OUTSIDE','|body|center|div|dl|fieldset|form|frameset|head|html|menu|noscript|ol|tbody|thead|tr|ul|caption|col|dd|dt|h1|h2|h3|h4|h5|h6|li|link|option|td|th|title|p|pre|');
define('WSHTML_NO_INDENT','|body|center|html|tbody|thead|');

class WhiteSpace_HTML extends _WhiteSpaceManager {
	var $indentChangers;
	var $_preformatted = false;
	var $_sets;
	function WhiteSpace_HTML($ws = "\t") {
		$this->_sets =& new UniqueSets();
		$this->indentChangers = $this->_sets->subtract(
			WSHTML_BLOCK_OUTSIDE, WSHTML_NO_INDENT
		);
		parent::_WhiteSpaceManager($ws);
	}
	function open(&$el) {
		// turn off formatting and handle <PRE>
		if ($el->tagName=='pre') {
			$this->_preformatted = true;
			return array($this->_i,'');
		}
		if ($this->_preformatted) {
			return array('','');
		}
		// cache
		$el->_whiteSpaceData['hasBlockChild'] = $this->_hasBlockChild($el);
		$el->_whiteSpaceData['isABlock'] = $this->_tagIn($el->tagName, WSHTML_BLOCK_OUTSIDE);
		$el->_whiteSpaceData['changeIndent'] = $this->_tagIn($el->tagName, $this->indentChangers);
		// before open tag
		$ws1 = ($el->_whiteSpaceData['isABlock'])? $this->_i : '';
		// after open tag
		$ws2 = ($el->_whiteSpaceData['hasBlockChild'])? "\n" : '';
		// increase indent after
		if ($el->_whiteSpaceData['changeIndent']) {
			$this->_changeIndent(1);
		}
		return array($ws1, $ws2);
	}
	function close(&$el) {
		// turn on formatting and handle </PRE>
		if ($el->tagName=='pre') {
			$this->_preformatted = false;
			return array('',"\n");
		}
		if ($this->_preformatted) {
			return array('','');
		}
		// decrease indent before
		if ($el->_whiteSpaceData['changeIndent']) {
			$this->_changeIndent(-1);
		}
		// before close tag
		$ws3 = ($el->_whiteSpaceData['hasBlockChild'])? $this->_i : '';
		// after close tag
		$ws4 = ($el->_whiteSpaceData['isABlock'])? "\n" : '';
		// cleanup cache
		$el->_whiteSpaceData = false;
		return array($ws3, $ws4);
	}
	function _hasBlockChild(&$el) {
		$i = 0;
		while ($child =& $el->_getChild($i++)) {
			if (is_a($child, '_Element')
				&& $this->_tagIn($child->tagName, WSHTML_BLOCK_OUTSIDE)) {
				return true;
			}
		}
		return false;
	}
	function _tagIn($tagName, $set) {
		return $this->_sets->contains(strtolower($tagName), $set);
	}
}

class WhiteSpace_XML extends _WhiteSpaceManager {
	function WhiteSpace_XML($ws = "\t") {
		parent::_WhiteSpaceManager($ws);
	}
	function open(&$el) {
		// cache
		$el->_whiteSpaceData['hasChildElement'] = $this->_hasChildElement($el);
		if ($el->_whiteSpaceData['hasChildElement']) {
			$array = array($this->_i,"\n");
			$this->_changeIndent(1);
			return $array;
		} else {
			return array($this->_i,'');
		}
	}
	function close(&$el) {
		if ($el->_whiteSpaceData['hasChildElement']) {
			$this->_changeIndent(-1);
			return array($this->_i,"\n");
		} else {
			return array('',"\n");
		}
		// cleanup cache
		$el->_whiteSpaceData = false;
	}	
	function _hasChildElement(&$el) {
		$i = 0;
		while ($child =& $el->_getChild($i++)) {
			if (is_a($child, '_Element')) {
				return true;
			}
		}
		return false;
	}

}

// utility
class UniqueSets {
	var $_separator;
	var $_preg_separator;
	
	function UniqueSets() {
		$this->_separator = '|';
		$this->_preg_separator = '\\|';
	}
	function subtract($pool, $remove) {
		return $this->toSet(
			array_diff(
				$this->toArray($pool)
				,$this->toArray($remove)
			)
		);
	}
	function add($a, $b) {
		return $this->toSet(
			array_unique(
				array_merge(
					$this->toArray($a)
					,$this->toArray($b)
				)
			)
		);
	}
	function contains($needle, $haystack) {
		return (strpos($haystack, "{$this->_separator}{$needle}{$this->_separator}")!== false);
	}
	function sorted($set) {
		$array = $this->toArray($set);
		sort($array);
		return $this->toSet($array);
	}
	function toArray($set) {
		return explode($this->_separator, 
			preg_replace("/^{$this->_preg_separator}|{$this->_preg_separator}$/",'', $set)
		);
	}
	function toSet($array) {
		return $this->_separator
			.join($this->_separator, $array)
			.$this->_separator;
	}
}

?>