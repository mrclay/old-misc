<?php

/**
 * Store (not encrypt) tamper-proof strings in an HTTP cookie
 *
 * <code>
 * $storage = new MrClay_CookieStorage(array(
 *     'secret' => '67676kmcuiekihbfyhbtfitfytrdo=op-p-=[hH8'
 * ));
 * if ($storage->store('user', 'id:62572,email:bob@yahoo.com,name:Bob')) {
 *    // cookie OK length and no complaints from setcookie()
 * } else {
 *    // check $storage->errors
 * }
 * 
 * // later request
 * $user = $storage->fetch('user');
 * if (is_string($user)) {
 *    // valid cookie
 *    $age = time() - $storage->getTimestamp('user');
 * } else {
 *     if (false === $user) {
 *         // data was altered!
 *     } else {
 *         // cookie not present
 *     }
 * }
 * </code>
 */
class MrClay_CookieStorage {

    // conservative storage limit considering variable-length Set-Cookie header
    const LENGTH_LIMIT = 3896;
    
    /**
     * @var array options
     */
    private $_o;

    private $_returns = array();

    /**
     * @var array errors that occured
     */
    public $errors = array();


    public function __construct($options = array())
    {
        $this->_o = array_merge(self::getDefaults(), $options);
    }

    public function getDefaults()
    {
        return array(
            'secret' => ''
            ,'domain' => ''
            ,'secure' => false
            ,'path' => '/'
            ,'expire' => '2147368447' // Sun, 17-Jan-2038 19:14:07 GMT (Google)
        );
    }

    public function setOption($name, $value)
    {
        $this->_o[$name] = $value;
    }

    /**
     * @return bool success
     */
    public function store($name, $str)
    {
        if (empty($this->_o['secret'])) {
            $this->errors[] = 'Must first set the option: secret.';
            return false;
        }
        $time = time();
        // tie sig to this cookie name
        $sig = sha1($this->_o['secret'] . $name . $time . $str);
        $raw = $sig . '.' . $time . '.' . $str;
        if (strlen($name . $raw) > self::LENGTH_LIMIT) {
            $this->errors[] = 'Cookie is likely too large to store.';
            return false;
        }
        $res = setcookie($name, $raw, $this->_o['expire'], $this->_o['path'], $this->_o['domain'], $this->_o['secure']);
        if ($res) {
            return true;
        } else {
            $this->errors[] = 'Setcookie() returned false. Headers may have been sent.';
            return false;
        }
    }

    /**
     * @return string null if cookie not set, false if tampering occured
     */
    public function fetch($name)
    {
        if (isset($this->_returns[$name])) {
            return $this->_returns[$name][0];
        }
        if (!isset($_COOKIE[$name])) {
            return null;
        }
        $cookie = get_magic_quotes_gpc()
            ? stripslashes($_COOKIE[$name])
            : $_COOKIE[$name];
        list($sig, $time, $str) = explode('.', $cookie, 3);
        if (strlen($time) < 10
            || strlen($sig) < 40
            || $sig !== sha1($this->_o['secret'] . $name . $time . $str)
        ) {
            $this->errors[] = 'Cookie was tampered with.';
            return false;
        }
        $this->_returns[$name] = array($str, $time);
        return $str;
    }

    public function getTimestamp($name)
    {
        if (is_string($this->fetch($name))) {
            return $this->_returns[$name][1];
        }
        return false;
    }

    public function delete($name)
    {
        setcookie($name, '', time() - 3600, $this->_o['path'], $this->_o['domain'], $this->_o['secure']);
    }
}

