<?php

class NoCms_Action {
    protected $_nocms = null;
    public $title = null;
    public $htmlRoot = null;
    
    public function __construct($nocmsObj)
    {
        $this->_nocms = $nocmsObj;
        $this->title = ucfirst(substr(get_class($this), 13));
        $this->htmlRoot = $this->_nocms->getConfig('htmlRoot');
    }
    
    public function html($content, $beforeBodyEnd = '') {
        $title = $this->title;
        $siteHome = $this->_nocms->getConfig('siteHome');
        $siteName = $this->_nocms->getConfig('siteName');
        $htmlRoot = $this->htmlRoot;
        $actionRoot = $this->htmlRoot . '/index.php';
        $loggedIn = (bool)$this->_nocms->user;
        
        include './template.php';
    }
    
    public function getPost($key, $default = null)
    {
        return self::_getRequestVar($_POST, $key, $default);
    }
    
    public function getGet($key, $default = null)
    {
        return self::_getRequestVar($_GET, $key, $default);
    }
    
    private static function _getRequestVar($array, $key, $default)
    {
        if (! isset($array[$key])) {
            return $default;
        }
        $val = $array[$key];
        return get_magic_quotes_gpc()
            ? self::_ssDeep($val)
            : $val;
    }
    
    private static function _ssDeep($value)
    {
        return is_array($value)
            ? array_map(array('NoCms_Action', '_ssDeep'), $value) 
            : stripslashes($value);
    }
}
