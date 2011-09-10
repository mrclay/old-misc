<?php

namespace MrClay\Crypt\Encoding;
use MrClay\Crypt\ByteString;

interface EncodingInterface {
    /**
     * @param \MrClay\Crypt\ByteString $bytes
     * @return string
     */
    public function encode(ByteString $bytes);

    /**
     * @param string $encoded
     * @return false|\MrClay\Crypt\ByteString
     */
    public function decode($encoded);

    /**
     * @return string
     */
    public function getSeparator();
}
