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
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_Hmac {

    protected $_rand;
    protected $_secret;
    protected $_hashAlgo = 'sha256';

    /**
     * iterations to perform during key derivation
     *
     * @var int
     */
    protected $_iterations = 5000;

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
        $this->setSecret($secretKey);
        $this->setHashAlgo($hashAlgo);
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
    public function sign($val, $saltLength = 16)
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
     * Set the secret from which a key will be derived
     * 
     * @param type $secret
     *
     * @return self
     */
    public function setSecret($secret)
    {
        $this->_secret = $secret;
        return $this;
    }

    public function setIterations($numIterations = 5000)
    {
        $this->_iterations = $numIterations;
    }

    /**
     * @param string $algo
     *
     * @return MrClay_Hmac
     *
     * @throw InvalidArgumentException
     */
    public function setHashAlgo($algo)
    {
        $algo = strtolower($algo);
        if (! in_array($algo, hash_algos())) {
            throw new InvalidArgumentException("Hash algorithm '$algo' unsupported.");
        }
        $this->_hashAlgo = $algo;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashAlgo()
    {
        return $this->_hashAlgo;
    }

    /**
     * Derive a key via PBKDF2 (described in RFC 2898)
     *
     * @param string $p password
     * @param string $s salt
     * @param int $c iteration count (use 1000 or higher)
     * @param int $kl derived key length
     * @param string $a hash algorithm
     *
     * @return string derived key
     *
     * @author Andrew Johnson
     * @link http://www.itnewb.com/v/Encrypting-Passwords-with-PHP-for-Storage-Using-the-RSA-PBKDF2-Standard
    */
    function pbkdf2($p, $s, $c, $kl, $a = 'sha256') {
        $hl = strlen(hash($a, null, true)); // Hash length
        $kb = ceil($kl / $hl);              // Key blocks to compute
        $dk = '';                           // Derived key
        // Create key
        for ($block = 1; $block <= $kb; $block++) {
            // Initial hash for this block
            $ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);
            // Perform block iterations
            for ($i = 1; $i < $c; $i++) {
                // XOR each iterate
                $ib ^= ($b = hash_hmac($a, $b, $p, true));
            }
            $dk .= $ib; // Append iterated block
        }
        // Return derived key of correct length
        return substr($dk, 0, $kl);
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
     *
     * @param string $salt
     * 
     * @return string 
     */
    protected function _digest($val, $salt)
    {
        $key = $this->pbkdf2($this->_secret, $salt, $this->_iterations, 32);
        $hash = hash_hmac($this->_hashAlgo, $val, $key, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
}