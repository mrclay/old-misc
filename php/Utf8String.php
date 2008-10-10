<?php

// http://sourceforge.net/projects/phputf8 required
if (! defined('UTF8')) {
    require_once 'utf8/utf8.php';
    require_once 'utf8/utils/validation.php';
    require_once 'utf8/utils/bad.php';
}

/** 
 * Immutable UTF-8 string class for PHP5, especially handy for handling UTF-8
 * input from web forms.
 * 
 * <code>
 * $title = Utf8String::input($_POST, 'title'); // 'āll īs & ōk'
 * if (false === $title) {
 *     // input missing 
 * } else {
 *     if ($title->wasCleaned) {
 *         // received (with invalid bytes stripped)
 *     }
 *     $ucTitle = $title->ucwords(); // 'Āll Īs & Ōk'
 * 
 *     // the "s" property is a shortcut for casting to string (or in PHP<5.2)
 *     $app->setTitle($ucTitle->s); // (string)$ucTitle
 * 
 *     // chainable
 *     $ucTitle->toAscii()->strtolower(); // 'all is & ok'
 * 
 *     // the "_" property applies htmlspecialchars
 *     echo $ucTitle->_; // 'Āll Īs &amp; Ōk'
 * }
 * </code>
 */
class Utf8String {
    
    /**
     * Option to strip out invalid bytes from UTF-8 string
     */
    const INVALID_STRIP = 1;
    
    /**
     * Option to replace invalid bytes in a UTF-8 string. The replacement
     * character is Utf8String::$replacement.
     */
    const INVALID_REPLACE = 2;
    
    /**
     * Single ASCII character used to replace invalid bytes in a UTF-8 string.
     * @var string
     */
    public static $replacement = '?';
    
    /**
     * Create a UTF-8 string object
     * @param string $str UTF-8 encoded string
     * @param int $onInvalid method of cleaning UTF-8 (default INVALID_STRIP)
     * @return Utf8String
     */
    public static function make($str = '', $onInvalid = self::INVALID_STRIP)
    {
        list($str, $wasCleaned) = self::validate($str, $onInvalid);
        return new Utf8String($str, false, $wasCleaned);
    }
    
    /**
     * Get a valid UTF-8 string from a request array
     *
     * @param array $array request array e.g. $_POST
     * @param string $key
     * @param mixed $default value to return if key isn't present in the
     * array. By default Utf8Input::$default will be returned.
     * @param int $onInvalid method of cleaning UTF-8 (default INVALID_STRIP)
     * @return mixed If $array[$key] was an array, an array of Utf8String
     * objects will be returned.
     */
    public static function input($array, $key, $default = false, $onInvalid = self::INVALID_STRIP)
    {
        if (isset($array[$key])) {
            $val = $array[$key];
            if (is_array($val)) {
                foreach ($val as $key2 => $val2) {
                    get_magic_quotes_gpc() && ($val2 = stripslashes($val2));
                    list($val2, $wasCleaned) = self::validate($val2, $onInvalid); 
                    $val[$key2] = new Utf8String($val2, false, $wasCleaned);
                }
                return $val;
            } else {
                get_magic_quotes_gpc() && ($val = stripslashes($val));
                list($val, $wasCleaned) = self::validate($val, $onInvalid);
                
                return new Utf8String($val, false, $wasCleaned);
            }
        }
        return $default;
    }
    
    /**
     * Validate a UTF-8 string
     *
     * @param string $str UTF-8 encoded string
     * @param int $onInvalid method of cleaning UTF-8 (default INVALID_STRIP)
     * @return array ($str, wasCleaned)
     */
    public static function validate($str, $onInvalid = self::INVALID_STRIP)
    {
        if (utf8_is_valid($str)) {
            return array($str, false);
        }
        switch ($onInvalid) {
            case self::INVALID_REPLACE: 
                return array(utf8_bad_replace($str, self::$replacement), true);
            default:
                return array(utf8_bad_strip($str), true);
        }
    }
    
    /**
     * Get an ASCII version of this string via basic transliteration
     * @return Utf8String
     */
    public function toAscii()
    {
        if ($this->_isAscii) {
            return new Utf8String(
                $this->_str
                ,true
                ,$this->_wasCleaned
            );
        }
        require_once 'utf8_to_ascii/utf8_to_ascii.php';
        return new Utf8String(
            utf8_to_ascii($this->_str, self::$replacement)
            ,true
            ,$this->_wasCleaned
        );
    }
    
    /**
     * Get a version of this string where non-ASCII and control characters
     * have been stripped
     * @return Utf8String
     */
    public function stripNonAscii()
    {
        if ($this->_isAscii) {
            return new Utf8String(
                $this->_str
                ,true
                ,$this->_wasCleaned
            );
        }
        require_once UTF8 . '/utils/ascii.php';
        return new Utf8String(
            utf8_strip_non_ascii_ctrl($this->_str)
            ,true
            ,$this->_wasCleaned
        );
    }
    
    /**
     * @return string The UTF-8 encoded string  
     */
    public function __toString()
    {
        return $this->_str;
    }
    
    /**
     * Public read-only properties
     * 
     * 's': (string) contents of the string
     * 'isAscii': (bool) is this string ASCII? (7 bit clean)
     * 'wasCleaned': (bool) did the string need to be cleaned by Utf8String
     * '_': (string) the string with HTML special chars escaped
     */
    public function __get($name)
    {
        switch ($name) {
            case 's': return $this->_str;
            case 'isAscii': return $this->_isAscii;
            case 'wasCleaned': return $this->_wasCleaned;
            case '_': return htmlspecialchars($this->_str, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Public methods
     * 
     * The argument list to each is identical to the native PHP function
     * except with the subject string argument removed.
     * 
     * If the return value is a string, it will be returned as a Utf8String
     * object.
     * 
     * When possible, native PHP methods are used.
     */
    public function __call($name, $args)
    {
        $methods = array(
            // func => in utf8 core, position of string arg, ascii propogates
            //    , utf8_ always reqd, utf8 library version
            'strlen' => array(true, 0, true, false, '')
            ,'strpos' => array(true, 0, true, true, '')
            ,'strtolower' => array(true, 0, true, false, '')
            ,'strtoupper' => array(true, 0, true, false, '')
            ,'substr' => array(true, 0, true, false, '')
            ,'ord' => array(false, 0, true, false, '')
            ,'str_ireplace' => array(false, 2, false, false, 'utf8_ireplace')
            ,'str_pad' => array(false, 0, false, true, '')
            ,'str_split' => array(false, 0, true, false, '')
            ,'strcasecmp' => array(false, 0, true, true, '')
            ,'strcspn' => array(false, 0, true, true, '')
            ,'stristr' => array(false, 0, true, true, '')
            ,'strrev' => array(false, 0, true, false, '')
            ,'strspn' => array(false, 0, true, true, true, '')
            ,'substr_replace' => array(false, 0, false, false, '')
            ,'trim' => array(false, 0, true, true, '')
            ,'ucfirst' => array(false, 0, true, false, '')
            ,'ucwords' => array(false, 0, true, false, '')
        );
        if (! isset($methods[$name])) {
            return;
        }
        list($inCore
            ,$thisArgPosition
            ,$asciiPropogates
            ,$utf8Requied
            ,$utf8FuncName
            ) = $methods[$name];
        array_splice($args, $thisArgPosition, 0, array($this->_str));
        if (! $inCore) {
            require_once UTF8 . '/' . $name . '.php';
        }
        if ($utf8Requied || ! $this->_isAscii) {
            $name = $utf8FuncName
                ? $utf8FuncName
                : 'utf8_' . $name;
        }
        $ret = call_user_func_array($name, $args);
        if (is_string($ret)) {
            $isAscii = ($asciiPropogates && $this->_isAscii);
            return new Utf8String($ret, $isAscii, $this->_wasCleaned);
        }
        return $ret;
    }
    
    private $_str = '';
    private $_isAscii = false;
    private $_wasCleaned = false;
    
    private function __construct($str, $knownAscii, $wasCleaned)
    {
        $this->_str = $str;
        $this->_wasCleaned = $wasCleaned;
        $this->_isAscii = $knownAscii
            ? true
            : (preg_match('/(?:[^\x00-\x7F])/', $this->_str) !== 1);
    }
}
