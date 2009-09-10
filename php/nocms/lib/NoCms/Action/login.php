<?php

class NoCms_Action_login extends NoCms_Action {
    
    public function GET()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_nocms->redirect();
        } else {
            $this->_showForm();
        }
    }
    
    public function POST()
    {
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate(
            new NoCms_AuthAdapter(
                $this->_nocms->getConfig('password')
                ,$this->_nocms->getConfig('passwordIsMd5')
            )
        );
        if ($result->isValid()) {
            $this->_nocms->redirect();
        } else {
            $this->_showForm($result->getMessages());
        }
    }
    
    private function _showForm($messages = array()) {
        header('Cache-Control: no-cache');
        if ($messages) {
            $msgs = '<ul>';
            foreach ($messages as $msg) {
                $msgs .= '<li>' . htmlspecialchars($msg) . '</li>';
            }
            $msgs .= '</ul>';
        }
        $salt = uniqid(mt_rand(), true);
        $this->html("
            <h1>Login to edit content</h1>
            {$msgs}
            <form action='' method='post'>
                <p><label>Password <input type='password' id='pwd' name='pwd' size='20'></label>
                  <input type='submit' value='Login'>
                  <input type='hidden' id='salt' name='salt' value='{$salt}'>
                </p>
            </form>
        ", 
        '<script src="../md5.js"></script>
        <script>
        $(function () {
            $("#pwd")[0].focus();
            $("form").submit(function () {
                var $pwd = $("#pwd");
                $pwd.val(MD5($pwd.val()));
                $pwd.val(MD5($("#salt").val() + $pwd.val()));
            });
        });
        </script>');
    }
}

