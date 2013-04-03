<?php

namespace MrClay\Crypt;

use MrClay\Crypt\EncodedRequest;
use MrClay\Crypt\Encryption;
use MrClay\Crypt\Encoding\Base64Url;

/**
 * Send/receive encrypted and signed values (JSON-encoded) over HTTP POST requests
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class EncryptedRequest extends EncodedRequest {

    protected $encryption = null;

    /**
     * @param Encryption $encryption
     */
    public function __construct($encryption)
    {
        $this->encryption = $encryption;
    }

    /**
     * Encrypt
     *
     * @param mixed $value
     *
     * @return string
     */
    public function encode($value)
    {
        return $this->encryption->encrypt(json_encode($value))->encode();
    }

    /**
     * Get valid JSON from an encrypted string
     *
     * @param string $str
     *
     * @param bool $returnJson return JSON instead of value in 2nd position
     *
     * @return array [success, value]
     */
    public function decode($str, $returnJson = false)
    {
        $result = $this->encryption->decrypt(Container::decode(new Base64Url(), $str));
        if ($result === false) {
            return array(false, null);
        }
        return array(true, $returnJson ? $result : json_decode($result, true));
    }
}
