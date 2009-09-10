<?php

class NoCms_AuthAdapter implements Zend_Auth_Adapter_Interface {
    private $_pwd = null;
    private $_md5 = null;
    
    public function __construct($password, $isMd5 = false)
    {
        $this->_pwd = $isMd5
            ? $password
            : md5($password);
        $this->_md5 = $isMd5;
    }
    
    public function authenticate()
    {
        $code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
        $messages = array();
        $identity = '';
        if ($this->_pwd === md5('')
            || !isset($_POST['pwd']) 
            || !isset($_POST['salt'])) {
            $messages[] = 'You must configure a password to login.';
        } else {
            $recieved = $_POST['pwd'];
            $expected = md5($_POST['salt'] . $this->_pwd);
            if ($recieved === $expected) {
                $code = Zend_Auth_Result::SUCCESS;
                $identity = 'admin';
            } else {
                $messages[] = 'Password incorrect. (Note: Javascript required)';
            }
        }
        return new Zend_Auth_Result($code, $identity, $messages);
    }
}
