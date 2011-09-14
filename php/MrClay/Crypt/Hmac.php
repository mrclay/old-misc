<?php

namespace MrClay\Crypt;

use MrClay\Crypt\ByteString;
use MrClay\Crypt\KeyDeriver;

/**
 * Wrapper around HMAC sign/verify process with unique salts and derived keys.
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
class Hmac {

    /**
     * @var string
     */
    public $hashAlgo = 'sha256';

    /**
     * @var string
     */
    protected $password;

    /**
     * @var \MrClay\Crypt\KeyDeriver
     */
    protected $keyDeriver;

    /**
     * @param string $password
     * @param KeyDeriver $deriver
     */
    public function __construct($password, KeyDeriver $deriver = null)
    {
        if (! $deriver) {
            $deriver = new KeyDeriver();
        }
        $this->password = $password;
        $this->keyDeriver = $deriver;
    }

    /**
     * @param string $str
     * @return Container [str, salt, mac]
     */
    public function sign($str)
    {
        list($key, $salt) = $this->keyDeriver->pbkdf2($this->password);
        $mac = $this->generateMac($str, $key);
        return new Container(array(new ByteString($str), $salt, $mac));
    }

    /**
     * @param Container $cont
     * @return bool
     */
    public function isValid(Container $cont)
    {
        if (count($cont) !== 3) {
            return false;
        }
        list($str, $salt, $mac) = $cont;
        list($key, $salt) = $this->keyDeriver->pbkdf2($this->password, $salt);
        return $this->generateMac($str->getBytes(), $key)->equals($mac);
    }

    /**
     * @param string $str
     * @param ByteString $key
     * @return ByteString
     */
    public function generateMac($str, ByteString $key)
    {
        $hmac = hash_hmac($this->hashAlgo, $str, $key->getBytes(), true);
        return new ByteString($hmac);
    }
}
