<?php

/**
 * Generate HTML tables from BD result arrays and other evil bits
 * 
 * This code is old, slow, poorly designed and documented, yet often comes in 
 * handy :) It's built to be able to generate nicely formatted markup, which
 * makes it a bit slower that straight string concats.
 * 
 * <code>
 * $html = new MrClay_Html();
 * echo $html->build_table_from_assoc_array($resultArray);
 * 
 * // slower but formatted markup:
 * $html->set_whiteSpace(true);
 * echo $html->build_table_from_assoc_array($resultArray);
 * </code>
 */
class MrClay_Html {
	public $charset = 'UTF-8';	// see http://php.net/htmlspecialchars
	public $quote_style = ENT_QUOTES;	// see http://php.net/htmlspecialchars
	
	public function __construct($xml = true) {
		$this->_xml = $xml;
		$this->_end_token = ($this->_xml)? ' />' : '>';
		$this->set_whiteSpace(false); // faster
	}

	// $data is array where each element is and associative array of column => value.
	// if $o['headings'] is provided, count($o['headings']) should equal the number of columns
	public function build_table_from_assoc_array($assocArray, $options = array()) {
		$o = array_merge(array(	// default options
			 'applyHtmlentities' => true // deprecated, use 'escapeValues'
            ,'escapeValues' => true
			,'caption' => ''
			,'attributes' => array(
				'class' => 'resultTable'
				,'border' => '1'
				,'cellpadding' => '4'
				,'cellspacing' => '0'
			)
			,'evenRowClass' => ''
			,'emptyCellContent' => '&nbsp;'
			,'headings' => array()
			,'includeThead' => true
			,'numRows' => false
			,'numRows_element' => 'div'
			,'numRows_attributes' => array('class'=>'numRows')
			,'radios' => false
			,'radios_cellAttributes' => array('class'=>'hasRadioInput')
			,'radios_inputName' => 'newId'
			,'radios_formatColumn' => '&nbsp;{value}'
			,'orientation' => 'vertical' // or 'horizontal'
			,'stringifyFunc' => array($this, 'stringify')
			,'attributorFunc' => false
		), $options);

        if (! $o['applyHtmlentities']) {
            $o['escapeValues'] = false;
        }
        unset($o['applyHtmlentities']);
        
		if (empty($o['headings']) && !empty($assocArray)) {
			$normalizedRowKeys = array_keys($assocArray);
			$o['headings'] = array_keys($assocArray[$normalizedRowKeys[0]]);
		}

		$caption = (!empty($o['caption']))? $this->hwrap($o['caption'],'caption') : '';

		$thead = ''; // thead
		if ($o['includeThead'] && $o['orientation'] == 'vertical') {
			foreach ($o['headings'] as $heading) {
				$thead .= $this->_table_cell($heading, 'th', $o);
			}
			$thead = $this->wrap($this->wrap($thead, 'tr'), 'thead');
		}

		// tbody
		if ($o['orientation'] == 'vertical') {
			$tbody = '';
			$isEven = false;
			foreach ($assocArray as $row) {
				$tbody .= $this->_TBODY_row($row, $o, $isEven);
				if ($o['evenRowClass']) {
					$isEven = !$isEven;
				}
			}
		} else {
			// horizontal
			$tbody = $this->_horizontal_TBODY($assocArray, $o);
		}
		$tbody = $this->wrap($tbody,'tbody');

		$table = $this->wrap($caption.$thead.$tbody,'table',$o['attributes']);

		if ($o['numRows']) {
			$table .= $this->open_tag($o['numRows_element'], $o['numRows_attributes'])
				.count($assocArray)." rows.</{$o['numRows_element']}>";
		}
		return ($this->_n=="\n")
			? $this->_indent_lines($table) . $this->_n
			: $table;
	}

	public function build_dl_from_row($row, $options = array()) {
		$o = array_merge(array(	// default options
			 'escapeValues' => true
			,'attributes' => array(
				'class' => 'resultDl'
			)
			,'headings' => array()
			,'stringifyFunc' => array($this, 'stringify')
		), $options);

		if (empty($o['headings'])) {
			$o['headings'] = array_keys($row);
		}

		$dl = '';
		foreach ($row as $key => $value) {
			$dl .= $this->_t . $this->wrap($key, 'dt');
            if (!is_string($value)) {
				$value = call_user_func($o['stringifyFunc'], $value);
                $dl .= $this->_t . $this->wrap($value, 'dd');
			} else {
    			if ('' === $value) {
    				$dl .= $this->_t . $this->wrap('&nbsp;', 'dd');
    			} else {
    				$dl .= $o['escapeValues']
    					? $this->_t . $this->hwrap($value, 'dd')
    					: $this->_t . $this->wrap($value, 'dd');
    			}
            }
		}
		$dl = $this->wrap($dl, 'dl', $o['attributes']);

		return ($this->_n=="\n")
			? $this->_indent_lines($dl)
			: $dl;
	}

    public function set_whiteSpace($ws) {
		$this->_n = $ws? "\n" : '';
		$this->_t = $ws? "\t" : '';
		$this->_i = ($ws && $this->_indentLevel)?
			str_repeat("\t",$this->_indentLevel) : '';
	}

	public function change_indent($delta = 0) {
		$this->set_indent(max(0,$this->_indentLevel += $delta));
	}

	public function set_indent($level) {
		$this->_indentLevel = $level;
		if ($this->_n == "\n") {
			$this->_i = str_repeat("\t",$this->_indentLevel);
		}
	}

	public function open_tag($element, $attributes = '', $minimized = false) {
		$endToken = $minimized
			? $this->_end_token
			: '>';
		return '<'.$element.$this->_expand_attributes($attributes).$endToken;
	}

	// return $contents wrapped in element with appropriate whitespace
	public function wrap($contents, $element, $attributes = '') {
		if (!$element) return $contents;

		$needle = '|'.strtolower($element).'|';

		if ($this->_n=='') {
			// skip to end
		}
		elseif (strpos(
			'|address|blockquote|center|div|dl|fieldset|form|frameset'
			.'|menu|noscript|ol|tbody|thead|tr|ul|', $needle)!==false) {
			// \n inside, \n after
			return $this->open_tag($element, $attributes)."\n{$contents}</{$element}>\n";
		}
		elseif (strpos('|caption|col|dd|dt|li|option|td|th|p|pre|', $needle)!==false) {
			// \n after
			return $this->open_tag($element, $attributes)."{$contents}</{$element}>\n";
		}
		elseif (strpos('|table|select|', $needle)!==false) {
			// \n inside
			return $this->open_tag($element, $attributes)."\n{$contents}</{$element}>";
		}
		// no whiteSpace
		return $this->open_tag($element, $attributes)."{$contents}</{$element}>";
	}

	// wrap with htmlspecialchars($contents)
	public function hwrap($contents, $element, $attributes = '') {
		return $this->wrap(
			htmlspecialchars($contents, $this->quote_style, $this->charset)
			,$element
			,$attributes
		);
	}

	// apply htmlspecialchars
	public function h($string) {
		return htmlspecialchars($string,$this->quote_style,$this->charset);
	}
    
    // apply html_entity_decode
	public function hd($string) {
        return html_entity_decode($string, $this->quote_style,$this->charset);
	}

	public function external_script($url) {
		return $this->wrap('', 'script', array('type'=>'text/javascript','src'=>$url));
	}

	public function external_styleSheet($url, $media='', $title='', $alternative=false, $import=false)
	{
		$attributes['type'] = 'text/css';
		if (!empty($media)) {
			$attributes['media'] = $media;
		}
		if (empty($title) && $alternative) {
			$title = 'Alternative Style';
		}
		if (!empty($title)) {
			$attributes['title'] = htmlspecialchars($title,$this->quote_style,$this->charset);
		}
		$attributes['rel'] = ($alternative)? 'Alternate StyleSheet' : 'StyleSheet';

		return $this->open_tag('link',$attributes, true);
	}

	public function pre_output($content) {
		return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'
			."\n<title>Untitled</title>\n<pre>{$content}</pre>";
	}

	/*
	$value2displays = array('1'=>'one','2'=>'two','3'=>'3');
	$selectedValues = array('1');
	*/
	public function build_select($attributes = '', $value2displays, $selectedValues = array()) {
		$selectContents = '';
		foreach ($value2displays as $value => $display) {
			$optionAttributes = array();
			if ($value != $display) {
				$optionAttributes['value'] = $value;
			}
			if (in_array($value, $selectedValues)) {
				$optionAttributes['selected'] = 'selected';
			}
			$selectContents .= $this->_t.$this->hwrap($display, 'option', $optionAttributes);
		}
		return ($this->_n=="\n")?
			$this->_indent_lines($this->wrap($selectContents, 'select', $attributes)).$this->_n
		  : $this->wrap($selectContents, 'select', $attributes);
	}


	// if at all possible, use the 'headings' option in the table instead...
	//$renameArray = array('id'=>'The ID','COUNT(*)'=>'How Many');
	public function rename_assoc_array_columns($assocArray, $renameArray) {
		$renameArrayKeys = array_keys($renameArray);
		$newArray = array();
		foreach ($assocArray as $i => $row) {
			foreach ($row as $key => $value) {
				$renameIndex = array_search($key, $renameArrayKeys);
				if ($renameIndex===NULL || $renameIndex===false) {
					$newArray[$i][$key] = $value;
				} else {
					$newArray[$i][$renameArray[$key]] = $value;
				}
			}
		}
		return $newArray;
	}

	// use NULL for format to remove a column
	public function format_assoc_array($assocArray, $formatArray = array()) {
        // build search array and ensure formatArray is complete
		$columns = array_keys($assocArray[0]);
		$searchArray = array();
		foreach ($columns as $column) {
			array_push($searchArray, '{'.$column.'}');
			array_push($searchArray, '{'.$column.':h}');
			array_push($searchArray, '{'.$column.':hd}');
			if (!array_key_exists($column, $formatArray)) {
				$formatArray[$column] = '{'.$column.'}';
			}
		}
		// each row
		foreach ($assocArray as $i => $row) {
			$replaceArray = array();
			foreach ($row as $value) {
				array_push($replaceArray, $value);
				array_push($replaceArray, $this->h($value));
				array_push($replaceArray, $this->hd($value));
			}
			// each column
			foreach ($formatArray as $column => $format) {
				if ($format === null) {
					unset($assocArray[$i][$column]);
				} else {
					// format all columns!
					$assocArray[$i][$column] = str_replace(
						$searchArray, $replaceArray, $format
					);
				}
			}
		}
		return $assocArray;
	}

	// simple HTML doc output
	public function begin_page($title = 'Untitled Document', $moreHead = '') {
		return "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0//EN\ "
			."\"http://www.w3.org/TR/REC-html40/strict.dtd\">\n"
			."<html>\n\t<head>\n\t\t<title>"
			.$this->h($title)
			."</title>\n\t{$moreHead}</head>\n<body>\n";
	}
	public function end_page() {
		return "</body></html>";
	}

    // must return raw HTML
	public function stringify($foo) {
		if (is_null($foo)) {
			return 'N/A';
		}
		if (is_bool($foo)) {
			return $foo ? 'Yes' : 'No';
		}
		if (is_numeric($foo)) {
			return (string)$foo;
		}
		return '<pre>' . $this->h(var_export($foo, 1)) . '</pre>';
	}

	private function _expand_attributes($attributes) {
		if (!is_array($attributes)) {
			return empty($attributes)? '' : ' '.trim($attributes);
		}
		$buffer = '';
		foreach ($attributes as $property => $value) {
			if ($value !== false) {
				$buffer .= ' '.$property.'="'.htmlspecialchars($value, ENT_QUOTES, $this->charset).'"';
			}
		}
		return $buffer;
	}

	private function _TBODY_row($row, $o, $isEven = false) {
		$buffer = '';
		if ($o['radios']) {
			$keys = array_keys($row);
			// create first cell
			$input = $this->open_tag('input', array(
				'type' => 'radio'
				,'name' => $o['radios_inputName']
				,'value' => $row[$keys[0]]
			), true);
			$buffer .= $this->_t.$this->wrap(
				$input.str_replace('{value}', $row[$keys[0]], $o['radios_formatColumn'])
				,'td'
				,$o['radios_cellAttributes']
			);
			// done with first cell
			array_splice($row,0,1);
		}
		foreach ($row as $value) {
			if (!is_string($value)) {
				$value = call_user_func($o['stringifyFunc'], $value);
                $tempO = $o;
                $tempO['escapeValues'] = false;
                $buffer .= $this->_table_cell($value, 'td', $tempO);
			} else {
                $buffer .= $this->_table_cell($value, 'td', $o);
            }
		}
		return $isEven
			? $this->wrap($buffer, 'tr', array(
					'class' => $o['evenRowClass']
				))
			: $this->wrap($buffer, 'tr');
	}

	private function _horizontal_TBODY($assocArray, $o) {
		$trs = array();
		foreach ($assocArray as $row) {
			foreach ($row as $key => $value) {
				if (!isset($trs[$key])) {
					$trs[$key] = '';
				}
				if (! is_string($value)) {
					$value = call_user_func($o['stringifyFunc'], $value);
				} elseif ($o['escapeValues']) {
					$value = $this->h($value);
				}
				$trs[$key] .= $this->_table_cell($value, 'td', $o);
			}
		}
		$headings = $o['headings'];
		foreach ($trs as $key => $str) {
			$trs[$key] = $this->wrap(
				$this->_table_cell(array_shift($headings), 'th', $o) . $str, 'tr'
			);
		}
		return join('', $trs);
	}

	private function _table_cell($contents, $element, $o) {
		$attrs = $o['attributorFunc']
		    ? call_user_func($o['attributorFunc'], $contents)
            : '';
        if (trim($contents) == '') {
			return $this->_t.$this->wrap($o['emptyCellContent'], $element, $attrs);
		}
		if (isset($o['escapeValues']) && !$o['escapeValues']) {
			return $this->_t.$this->wrap($contents, $element, $attrs);
		}
		return $this->_t.$this->hwrap($contents, $element, $attrs);
	}

	// only call if whiteSpace is used!
	private function _indent_lines($string) {
		return $this->_i.str_replace("\n", "\n".$this->_i, $string);
	}
    
    private $_xml;
	private $_end_token;
	private $_t;			// tab or ''
	private $_n;			// newline or ''
	private $_i;			// multiple tabs or ''
	private $_indentLevel = 0;
}
