<?php

class NoCms_Action_logout extends NoCms_Action {
    
    public function GET()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_nocms->redirect('/login');
    }
    
}

