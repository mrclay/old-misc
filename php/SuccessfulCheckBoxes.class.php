<?php
/* SuccessfulCheckBoxes.class.php (Created ~ May 2006)

From: http://www.w3.org/TR/html401/interact/forms.html#checkbox
"When a form is submitted, only "on" checkbox controls can become successful"

On the form handling side, this is annoying!  With this class, all checkboxes are 
successful, returning "on" or "off".  Or, optionally, they can return "0" or "1";

USAGE:

//form page
$SCB = new SuccessfulCheckBoxes();
echo $SCB->check('status');

//form handler
$SCB = new SuccessfulCheckBoxes();
// $_POST['status'] is 'on' or 'off'!

SETTING OPTIONS:

$SCB = new SuccessfulCheckBoxes(array('values01' => true));
// $_POST['status'] is '0' or '1'!

*/

class SuccessfulCheckBoxes {
	var $isXHTML;
	var $nameToId;
	var $arrayName;
	var $values01;
	var $version;
	
	function SuccessfulCheckBoxes($givenOptions = array()) {
		$this->version = '1.0';
		$this->_extract_options($givenOptions, array(
			'arrayName'		=> "SCB" 	// HTML name of hidden inputs
			,'isXHTML'		=> false	// XHTML markup
			,'nameToId'		=> true		// copy name to id attribute
			,'values01' 	=> false	// change $_POST values to 0/1
			,'lightMarkup'	=> false	// use combined input for names
		));
		$this->_success();
	}
	function _extract_options($givenOptions, $defaultOptions) {
		$options = array_merge($defaultOptions, $givenOptions);
		// ensure only names of default options aren't extracted
		foreach ($defaultOptions as $name => $value) {
			$this->$name = $options[$name];
		}
	}

	function check($name, $isChecked=false, $otherQuotedAttributes='') {
		$endToken = ($this->isXHTML)? ' />' : '>';
		$idAttribute = ($this->nameToId)? " id='{$name}'" : '';
		$checkedAttribute = ($isChecked)? ' checked="checked"' : '';
		return
			"<input type='hidden' name='{$this->arrayName}[]' value='{$name}'{$endToken}"
			."<input type='checkbox' name='{$name}'{$idAttribute}{$checkedAttribute}"
			." {$otherQuotedAttributes}{$endToken}";
	}
	
	// make checkBoxes 'successful'
	function _success() {
		if (!isset($_POST[$this->arrayName])) return; // bail if no POST
		
		$offValue = ($this->values01)? '0' : 'off';
		$onValue  = ($this->values01)? '1' : 'on';
		foreach ($_POST[$this->arrayName] as $name) {
			$_POST[$name] = (isset($_POST[$name]))? $onValue : $offValue;
		}
		
		unset($_POST[$this->arrayName]); // hide dirty work
	}
}

?>