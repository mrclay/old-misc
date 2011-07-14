<?php

/**
 * Wrapper around HMAC sign/verify process with unique salts and more compact salt/hashes.
 * 
 * USAGE
 * <code>
 * // signing
 * $tuple = $hmac->sign($value);
 * 
 * // validating
 * if ($hmac->isValid($tuple)) {
 *    // original value is $tuple[0]
 * } 
 * </code>
 * 
 * @link http://en.wikipedia.org/wiki/HMAC
 * 
 * @author Steve Clay <steve@mrclay.org>
 */
class MrClay_Hmac {

    protected $_rand;
    protected $_key;
    protected $_hashAlgo;

    /**
     * @param string $secretKey
     * 
     * @param string $hashAlgo 
     * 
     * @param MrClay_Rand $rand
     */
    public function __construct($secretKey, $hashAlgo = 'sha256', MrClay_Rand $rand = null) 
    {
        if (! $rand) {
            $rand = new MrClay_Rand();
        }
        $this->_rand = $rand;
        $this->_key = $secretKey;
        $this->_hashAlgo = $hashAlgo;
    }
    
    /**
     * Create an array containing the value given, a salt, and a hash created with the key.
     * 
     * @param mixed $val
     * 
     * @param int $saltLength
     * 
     * @return array [value, salt, hash]
     */
    public function sign($val, $saltLength = 10)
    {
        $origVal = $val;
        if (! is_string($val)) {
            $val = serialize($val);
        }
        $salt = $this->createSalt($saltLength);
        $hash = $this->_digest($val, $salt);
        return array($origVal, $salt, $hash);
    }
    
    /**
     * Was the first value in the array likely passed to sign()?
     * 
     * Caveat: If your value's serialization is not deterministic, validation may fail.
     * 
     * @param array $valueSaltHash [value, salt, hash]
     * 
     * @return bool 
     */
    public function isValid(array $valueSaltHash)
    {
        list($val, $salt, $hash) = $valueSaltHash;
        if (! is_string($val)) {
            $val = serialize($val);
        }
        return ($hash === $this->_digest($val, $salt));
    }
    
    /**
     * Create a string of random chars within [a-z A-z - _]
     * 
     * @param int $length length of output string
     * 
     * @return string
     */
    public function createSalt($length)
    {
        return $this->_rand->getUrlSafeChars($length);
    }
    
    /**
     * base 64 encoding with URL-safe chars and no padding (=)
     * 
     * @link http://en.wikipedia.org/wiki/Base64#URL_applications
     * 
     * @param string $data
     * 
     * @return string
     */
    public static function base64url($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Get a hash of a string with the key and salt
     * 
     * @param string $val
     * @param string $salt
     * @return string 
     */
    protected function _digest($val, $salt)
    {
        $key = $this->_key . $salt;
        $hash = hash_hmac($this->_hashAlgo, $val, $key, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
}