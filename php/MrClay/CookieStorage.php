<?php

/**
 * Store tamper-proof strings in an HTTP cookie
 * 
 * Requires MrClay_Hmac (and MrClay_Rand)
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
 * 
 * // encrypt cookie contents
 * $storage = new MrClay_CookieStorage(array(
 *     'secret' => '67676kmcuiekihbfyhbtfitfytrdo=op-p-=[hH8'
 *     ,'mode' => MrClay_CookieStorage::MODE_ENCRYPT
 * ));
 * </code>
 * 
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_CookieStorage {

    // conservative storage limit considering variable-length Set-Cookie header
    const LENGTH_LIMIT = 3896;
    const MODE_VISIBLE = 0;
    const MODE_ENCRYPT = 1;
    
    /**
     * @var array errors that occured
     */
    public $errors = array();


    public function __construct($options = array(), MrClay_Hmac $hmac = null)
    {
        $this->_o = array_merge(self::getDefaults(), $options);
        if (empty($this->_o['secret'])) {
            throw new Exception('secret must be set in $options.');
        }
        if (! $hmac) {
            $hmac = new MrClay_Hmac($this->_o['secret'], $this->_o['hashAlgo']);
        } else {
            $hmac->setKey($this->_o['secret']);
        }
        $this->_hmac = $hmac;
    }
    
    /*public static function hash($input)
    {
        return str_replace('=', '', base64_encode(hash('ripemd160', $input, true)));
    }*/
    
    public static function encrypt($key, $str)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);  
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);  
        $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, $iv);
        return base64_encode($data);
    }
    
    public static function decrypt($key, $data)
    {
        if (false === ($data = base64_decode($data))) {
            return false;
        }
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);  
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);  
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv);
    }

    public function getDefaults()
    {
        return array(
            'secret' => ''
            ,'domain' => ''
            ,'secure' => false
            ,'path' => '/'
            ,'expire' => '2147368447' // Sun, 17-Jan-2038 19:14:07 GMT (Google)
            //,'hashFunc' => array('MrClay_CookieStorage', 'hash')
            ,'encryptFunc' => array('MrClay_CookieStorage', 'encrypt')
            ,'decryptFunc' => array('MrClay_CookieStorage', 'decrypt')
            ,'hashAlgo' => 'ripemd160'
            ,'mode' => self::MODE_VISIBLE
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
        return ($this->_o['mode'] === self::MODE_ENCRYPT)
            ? $this->_storeEncrypted($name, $str)
            : $this->_store($name, $str);
    }
    
    private function _store($name, $str)
    {
        $time = base_convert($_SERVER['REQUEST_TIME'], 10, 36); // pack time
        // tie sig to this cookie name and timestamp
        list($val, $salt, $hash) = $this->_hmac->sign($name . $time . $str);
        
        $raw = $salt . '|' . $hash . '|' . $time . '|' . $str;
        if (strlen($name . $raw) > self::LENGTH_LIMIT) {
            $this->errors[] = 'Cookie is likely too large to store.';
            return false;
        }
        $res = setcookie($name, $raw, $this->_o['expire'], $this->_o['path'], 
                         $this->_o['domain'], $this->_o['secure']);
        if ($res) {
            return true;
        } else {
            $this->errors[] = 'Setcookie() returned false. Headers may have been sent.';
            return false;
        }
    }
    
    private function _storeEncrypted($name, $str)
    {
        if (! is_callable($this->_o['encryptFunc'])) {
            $this->errors[] = 'Encrypt function not callable';
            return false;
        }
        $time = base_convert($_SERVER['REQUEST_TIME'], 10, 36); // pack time
        
        // tie sig to this cookie name and timestamp
        list($val, $salt, $hash) = $this->_hmac->sign($name . $time . $str);
        
        $cryptKey = hash('ripemd160', $this->_o['secret'], true);
        $encrypted = call_user_func($this->_o['encryptFunc'], $cryptKey, '1' . $str);
        
        $raw = $salt . '|' . $hash . '|' . $time . '|' . $encrypted;
        if (strlen($name . $raw) > self::LENGTH_LIMIT) {
            $this->errors[] = 'Cookie is likely too large to store.';
            return false;
        }
        $res = setcookie($name, $raw, $this->_o['expire'], $this->_o['path'], 
                         $this->_o['domain'], $this->_o['secure']);
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
        if (!isset($_COOKIE[$name])) {
            return null;
        }
        return ($this->_o['mode'] === self::MODE_ENCRYPT)
            ? $this->_fetchEncrypted($name)
            : $this->_fetch($name);
    }
    
    private function _fetch($name)
    {
        if (isset($this->_returns[self::MODE_VISIBLE][$name])) {
            return $this->_returns[self::MODE_VISIBLE][$name][0];
        }
        $cookie = get_magic_quotes_gpc()
            ? stripslashes($_COOKIE[$name])
            : $_COOKIE[$name];
        $parts = explode('|', $cookie, 4);
        if (4 !== count($parts)) {
            $this->errors[] = 'Cookie was tampered with.';
            return false;
        }
        list($salt, $hash, $time, $str) = $parts;
        
        if (! $this->_hmac->isValid(array($name . $time . $str, $salt, $hash))) {
            $this->errors[] = 'Cookie was tampered with.';
            return false;
        }
        $time = base_convert($time, 36, 10); // unpack time
        $this->_returns[self::MODE_VISIBLE][$name] = array($str, $time);
        return $str;
    }
    
    private function _fetchEncrypted($name)
    {
        if (isset($this->_returns[self::MODE_ENCRYPT][$name])) {
            return $this->_returns[self::MODE_ENCRYPT][$name][0];
        }
        if (! is_callable($this->_o['decryptFunc'])) {
            $this->errors[] = 'Decrypt function not callable';
            return false;
        }
        $cookie = get_magic_quotes_gpc()
            ? stripslashes($_COOKIE[$name])
            : $_COOKIE[$name];
        $parts = explode('|', $cookie, 4);
        if (4 !== count($parts)) {
            $this->errors[] = 'Cookie was tampered with.';
            return false;
        }
        list($salt, $hash, $time, $encrypted) = $parts;
        
        $cryptKey = hash('ripemd160', $this->_o['secret'], true);
        $str = call_user_func($this->_o['decryptFunc'], $cryptKey, $encrypted);
        if (! $str) {
            $this->errors[] = 'Cookie was tampered with.';
            return false;
        }
        $str = substr($str, 1); // remove leading "1"
        $str = rtrim($str, "\x00"); // remove trailing null bytes
        
        if (! $this->_hmac->isValid(array($name . $time . $str, $salt, $hash))) {
            $this->errors[] = 'Cookie was tampered with.';
            return false;
        }
        $time = base_convert($time, 36, 10); // unpack time
        $this->_returns[self::MODE_ENCRYPT][$name] = array($str, $time);
        return $str;
    }

    public function getTimestamp($name)
    {
        if (is_string($this->fetch($name))) {
            return $this->_returns[$this->_o['mode']][$name][1];
        }
        return false;
    }

    public function delete($name)
    {
        setcookie($name, '', time() - 3600, $this->_o['path'], $this->_o['domain'], $this->_o['secure']);
    }
    
    /**
     * @var array options
     */
    private $_o;

    private $_returns = array();
    
    /**
     * @var MrClay_Hmac
     */
    protected $_hmac;
}

