<?php
/**
 * Class MrClay_Template
 *
 * @package MrClay
 */

require_once 'Savant3.php';

/**
 * Savant3 Extension with some extras
 *
 * @see Savant3
 */
class MrClay_Template extends Savant3 {

	/**
	 * names of open output buffers
	 *
	 * @var array
	 */
	private $_obStack = array();

	/**
	 * Constructor
	 *
	 * @param array $conf		configuration options (passed on to Savant3())
	 * @return void
	 * @see Savant3::__construct()
	 */
	public function __construct($conf = array())
	{
		parent::__construct($conf);
	}

	/**
	 * Echo a property if it exists, otherwise echo a given string
	 *
	 * @param string $prop				name of template property
	 * @param string $defaultString		echoed if property doesn't exist
	 * @return void
	 */
	public function echoDefault($prop, $defaultString = '')
	{
		echo (isset($this->$prop) ? $this->$prop : $defaultString);
	}

	/**
	 * Echo an escaped property if it exists, otherwise echo a given string
	 *
	 * @param string $prop				name of template property
	 * @param string $defaultString		echoed if property doesn't exist
	 * @return void
	 * @see Savant3::eprint()
	 */
	public function eprintDefault($prop, $defaultString = '')
	{
		if (!isset($this->$prop)) {
			echo $defaultString;
			return;
		}
		$args = array_slice(func_get_args(), 2);
		array_unshift($args, $this->$prop);
		return call_user_func_array(
			array($this, 'eprint'),
			$args
		);
	}

	/**
	 * Echo a property if it exists, otherwise load a template
	 *
	 * @param string $prop		name of template property
	 * @param string $file		template to be loaded if property doesn't exist
	 * @return void
	 * @see Savant3::template()
	 */
	public function echoLoad($prop, $file)
	{
		if (isset($this->$prop)) {
			echo $this->$prop;
		} else {
			include $this->template($file);
		}
	}

	/**
	 * Echo an escaped property if it exists, otherwise load a template
	 *
	 * @param string $prop		name of template property
	 * @param string $file		template to be loaded if property doesn't exist
	 * @return void
	 * @see Savant3::template()
	 */
	public function eprintLoad($prop, $file)
	{
		if (!isset($this->$prop)) {
			include $this->template($file);
			return;
		}
		$args = array_slice(func_get_args(), 2);
		array_unshift($args, $this->$prop);
		return call_user_func_array(
			array($this, 'eprint'),
			$args
		);
	}

	/**
	 * Begin output buffering
	 *
	 * The buffer will be later assigned to <var>$this->$prop</var> when end(), endDisplay(), 
	 * or endFetch() is called.
	 *
	 * <code>
	 * // assign the next buffer to $tpl->mainContent
	 * $tpl->begin('mainContent');
	 * ?> Hello World! <?php
	 * $tpl->end();
	 * </code>
	 *
	 * @param string $prop		property name
	 * @return void
	 * @see end()
	 */
	public function begin($prop)
	{
		$this->_obStack[] = $prop;
		ob_start();
	}

	/**
	 * End output buffering (assigning contents to the property specified in start())
	 *
	 * @return void
	 * @see begin()
	 */
	public function end()
	{
		$obProp = array_pop($this->_obStack);
		$this->$obProp = $this->_extract_properties(ob_get_contents());
		ob_end_clean();
	}

    /**
     * Extract various properties from the contents of a template property.
     *
     * Current extractions:
     *
     * The contents of a single title element (lowercase, no attributes or
     * whitespace inside the tags) will be extracted to $tpl->pageTitle
     *
     * The content of all occurences of style elements (with only the attribute
     * type="text/css") are combined to $tpl->moreCss
     *
     * @param string $prop
     * @return null
     */
	public function extractPropsFrom($prop)
	{
	    $this->$prop = $this->_extract_properties($this->$prop);
	}

    /**
	 * Set various properties based on contents of <var>$buffer</var>
	 *
	 * @param string $buffer
	 * @return string
	 */
	private function _extract_properties($buffer)
	{
        // combine style blocks, place CSS in $this->moreCss
		$css = '';
		$styleStart = strpos($buffer, '<style type="text/css">');
		$styleEnd = strpos($buffer, '</style>');
		while (false !== $styleStart && $styleEnd >= ($styleStart + 23)) {
			// has style block
			$cssStart = ($styleStart + 23);
			$css .= substr($buffer, $cssStart, ($styleEnd - $cssStart));
			$buffer = substr($buffer, 0, $styleStart)
			 . substr($buffer, $styleEnd + 8);

			// check for another style block
			$styleStart = strpos($buffer, '<style type="text/css">');
			$styleEnd = strpos($buffer, '</style>');
		}
		if (!isset($this->moreCss)) {
			$this->moreCss = '';
		}
		$this->moreCss .= $css;

        // find <title>Title</title> and place in $this->title
        $titleStart = strpos($buffer, '<title>');
		$titleEnd = strpos($buffer, '</title>');
		if (false !== $titleStart && $titleEnd >= ($titleStart + 7)) {
		    // has title
		    $titleTextStart = ($titleStart + 7);
		    $this->title = substr($buffer, $titleTextStart, ($titleEnd - $titleTextStart));
		    $buffer = substr($buffer, 0, $titleStart)
		      . substr($buffer, $titleEnd + 8);
		}

		return $buffer;
	}

	/**
	 * End output buffering, copy buffer to property and display template
	 *
	 * Buffer is assigned to the property designated in begin().
	 * @return void
	 */
	public function endDisplay()
	{
		$obProp = array_pop($this->_obStack);
		$this->$obProp = $this->_extract_properties(ob_get_contents());
		ob_end_clean();
		$this->display();
	}

	/**
	 * End output buffering, copy buffer to property and fetch template
	 *
	 * Buffer is assigned to the property designated in begin().
	 * @return string
	 */
	public function endFetch()
	{
		$obProp = array_pop($this->_obStack);
		$this->$obProp = $this->_extract_properties(ob_get_contents());
		ob_end_clean();
		return $this->fetch();
	}

}
