<?php

namespace MrClay\Crypt;

use MrClay\Crypt\ByteString;

/**
 * Wrapper around HMAC.
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
     * @var ByteString
     */
    protected $key;

    /**
     * @param ByteString $key
     */
    public function __construct(ByteString $key)
    {
        $this->key = $key;
    }

    /**
     * @param string $str
     * @return Container [str, mac]
     */
    public function sign($str)
    {
        $mac = $this->generateMac($str);
        return new Container(array(new ByteString($str), $mac));
    }

    /**
     * @param Container $cont
     * @return bool
     */
    public function isValid(Container $cont)
    {
        if (count($cont) !== 2) {
            return false;
        }
        list($str, $mac) = $cont;
        /* @var ByteString $str */
        /* @var ByteString $mac */
        return $this->generateMac($str->getBytes())->equals($mac);
    }

    /**
     * @param string $str
     * @return ByteString
     */
    public function generateMac($str)
    {
        $hmac = hash_hmac($this->hashAlgo, $str, $this->key->getBytes(), true);
        return new ByteString($hmac);
    }
}
