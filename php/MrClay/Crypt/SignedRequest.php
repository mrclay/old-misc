<?php

namespace MrClay\Crypt;

use MrClay\Crypt\EncodedRequest;
use MrClay\Crypt\Hmac;
use MrClay\Crypt\ByteString;

/**
 * Send/receive HMAC signed values (JSON-encoded) over HTTP POST requests
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class SignedRequest extends EncodedRequest {

    /**
     * @var Hmac
     */
    protected $hmac;

    /**
     * @param Hmac $hmac
     */
    public function __construct(Hmac $hmac)
    {
        $this->hmac = $hmac;
    }

    /**
     * Encode and sign
     *
     * @param mixed $value
     *
     * @return string
     */
    public function encode($value)
    {
        return $this->hmac->sign(json_encode($value))->encode();
    }

    /**
     * Get valid JSON from a signed string
     *
     * @param string $str
     *
     * @param bool $returnJson return JSON instead of value in 2nd position
     *
     * @return array [isValid, value]
     */
    public function decode($str, $returnJson = false)
    {
        $cont = Container::decode(new Encoding\Base64Url(), $str);
        if (! $cont || count($cont) !== 2) {
            $this->error = 'Invalid format';
            return array(false, null);
        }
        if (! $this->hmac->isValid($cont)) {
            $this->error = 'Hash invalid';
            return array(false, null);
        }
        /* @var ByteString[] $cont */
        $json = $cont[0]->getBytes();
        return array(true, $returnJson ? $json : json_decode($json, true));
    }
}
