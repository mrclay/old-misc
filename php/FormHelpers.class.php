<?php

/* Before there was HTML_QuickForm, before Zend_Form.. there were crappy attempts to
   make auto-repopulating forms easier. This is one of them! ~ 2006
*/

class FormHelpers {
	function FormHelpers() {}
	
	// allow echo from _POST
	function pfield($name, $default = '', $callback = 'htmlspecialchars', $callbackParams = array(ENT_QUOTES)) {
		return FormHelpers::_field($name, '_POST', $default, $callback, $callbackParams);
	}
	
	// allow echo from _GET
	function gfield($name, $default = '', $callback = 'htmlspecialchars', $callbackParams = array(ENT_QUOTES)) {
		return FormHelpers::_field($name, '_GET', $default, $callback, $callbackParams);
	}
	
	function _field($name, $selectFrom, $default, $callback, $callbackParams) {
		if ($selectFrom) {
			if ($selectFrom == '_POST') {
				$selectFrom =& $_POST;
			} else {
				$selectFrom =& $_GET;
			}
			if (isset($selectFrom[$name])) {
				$value = FormHelpers::magic_unquote($selectFrom[$name]);
			}
		}
		if (!isset($value)) {
			$value = $default;
		}
		if ($callback) {
			array_unshift($callbackParams, $value);
			$value = call_user_func_array($callback, $callbackParams);
		}
		return $value;
	}	

	function select_element($givenOptions = array()) {
		$defaultOptions = array(
			'name' 			=> 'mySelect'	// string required
			,'values'		=> array()		// array required
			,'labels'		=> false		// if array provided, must be same size as values
			,'selectedValues' => array()	// value(s) to be selected
			,'selectFrom'	=> false	// or '_POST' or '_GET'
			,'id'  			=> false	// use name by default
			,'multiple' 	=> false	// multiple attribute ?
			,'size' 		=> false	// size attribute
			,'otherQuotedAttributes' => false
		);
		$o = array_merge($defaultOptions, $givenOptions);
	
		if ($o['id'] === false)
			$o['id'] = $o['name'];
	
		$o['multiple'] = ($o['multiple'])? ' multiple="multiple"' : '';
		$o['size'] = ($o['size']!==false)? " size=\"{$o['size']}\"" : '';
		
		if ($o['multiple']) {
			$o['multiple'] = ' multiple="multiple"';
			$o['name'] .= '[]';
		} else {
			$o['multiple'] = '';
		}
		
		if ($o['selectFrom']) {
			if ($o['selectFrom'] == '_POST') {
				$selectFrom =& $_POST;
			} else {
				$selectFrom =& $_GET;
			}
			if (isset($selectFrom[$o['id']])) {
				$o['selectedValues'] = 
					FormHelpers::magic_unquote($selectFrom[$o['id']]);
			}
		}
		if (!is_array($o['selectedValues'])) {
			$o['selectedValues'] = array($o['selectedValues']);
		}
		
		$labels = $o['labels'] ? $o['labels'] : $o['values'];
		
		$buffer = "<select name=\"{$o['name']}\" id=\"{$o['id']}\"{$o['multiple']}{$o['size']} {$o['otherQuotedAttributes']}>";
		foreach ($o['values'] as $key => $value) {
			$selectedAttr = (in_array($value, $o['selectedValues']))	?
				' selected="selected"' : '';
			$valueAttr = ($value == $labels[$key])?
				'' : " value=\"".htmlspecialchars($value)."\"";
			$buffer .= "<option{$valueAttr}{$selectedAttr}>".htmlspecialchars($labels[$key])."</option>";
		}
		$buffer .= "</select>";
		return $buffer;
	}
	
	function radio_input($givenOptions = array()) {
		$defaultOptions = array(
			'name' 			=> 'myInput'	// string required
			,'value'		=> ''			// string required
			,'checked'		=> ''
			,'checkFrom'	=> false	// or '_POST' or '_GET'
			,'id'  			=> ''		// none by default
		);
		$o = array_merge($defaultOptions, $givenOptions);

		$idAttr = (!empty($o['id'])) ? " id='{$o['id']}'" : "";
		
		$checkedAttr = '';
		if ($o['checked']) {
			$checkedAttr = " checked='checked'";
		} else {
			if ($o['checkFrom']) {
				if ($o['checkFrom'] == '_POST') {
					$checkFrom =& $_POST;
				} else {
					$checkFrom =& $_GET;
				}
				if (isset($checkFrom[$o['name']]) && ($checkFrom[$o['name']] == $o['value']) ) {
					$checkedAttr = " checked='checked'";
				}
			}
		}
		
		return "<input type='radio' name='{$o['name']}' value='{$o['value']}'"
			. "{$checkedAttr}{$idAttr} />";
	}
	
	// return an associative array of radio inputs where
	// the array keys are the string value attributes of the input elements
	function radio_group($givenOptions = array()) {
		$defaultOptions = array(
			'name' 			 => 'myInput'	// string required
			,'values'		 => array()		// array required
			,'checkedValue' => false		// optional string (false = none)
			,'ids'			 => array()		// optional array of id attributes
		);
		$o = array_merge($defaultOptions, $givenOptions);

		$radios = array();
		foreach ($o['values'] as $key => $value) {
			$checked = (
				$o['checkedValue'] !== false 
				&& $o['checkedValue'] == $value
			);
			$id = isset($o['ids'][$key]) 
				? $o['ids'][$key] 
				: '';
			$radios[$value] = $this->radio_input(array(
				'name' => $o['name']
				,'value' => $value
				,'checked' => $checked
				,'id' => $id
			));
		}
		return $radios;
	}
	
	function magic_unquote($str) {
		if (!get_magic_quotes_gpc()) return $str;
		return is_array($str) 
			? array_map(array('FormHelpers', 'magic_unquote'), $str)
			: stripslashes($str);
	}
}

/*// TESTING
if (count(get_included_files())==1) {
	$fh =& new FormHelpers;
	echo "<form action='' method='post'>";
	echo 'Choose a Fred ' . $fh->select_element(array(
		'name' => 'Fred'
		,'values' => array(1, 2)
		,'labels' => array('Fred Thomas','Fred Parker')
		,'selectFrom' => '_POST'
		,'multiple' => true
		,'size' => 2
	));
	echo "<br>Name <input type='text' name='Name' value=\"" . 
		$fh->pfield('Name') . "\"'><br>";
	echo '<textarea name="Message">' . $fh->pfield('Message', '(Your message here)') . '</textarea>';
	echo "<input type='submit'>";
}
//*/

?>