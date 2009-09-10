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
        $home = $this->_nocms->getConfig('siteHome');
        $htmlRoot = $this->htmlRoot;
        $actionRoot = $this->htmlRoot . '/index.php';
        $loggedIn = (bool)$this->_nocms->user;
        
        include './template.php';
    }
}
