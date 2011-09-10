<?php

namespace MrClay\Crypt\Encoding;

use MrClay\Crypt\ByteString;

/**
 * @link http://en.wikipedia.org/wiki/Base64#URL_applications
 */
class Base64Url implements EncodingInterface {
    /**
     * @param \MrClay\Crypt\ByteString $bytes
     * @return string
     */
    public function encode(ByteString $bytes)
    {
        return rtrim(strtr(base64_encode($bytes->getBytes()), "+/", "-_"), '=');
    }

    /**
     * @param string $encoded
     * @return false|\MrClay\Crypt\ByteString
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
