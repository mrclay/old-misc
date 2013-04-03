<?php

namespace MrClay\Crypt\Encoding;

use MrClay\Crypt\ByteString;

/**
 * @link http://en.wikipedia.org/wiki/Base64#URL_applications
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class Base64Url implements EncodingInterface {
    /**
     * @param ByteString $bytes
     * @return string
     */
    public function encode(ByteString $bytes)
    {
        return rtrim(strtr(base64_encode($bytes->getBytes()), "+/", "-_"), '=');
    }

    /**
     * @param string $encoded
     * @return false|ByteString
     */
    public function decode($encoded)
    {
        $decoded = base64_decode(strtr($encoded, "-_", "+/"));
        return ($decoded === false)
            ? false
            : new ByteString($decoded);
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return ".";
    }
}
