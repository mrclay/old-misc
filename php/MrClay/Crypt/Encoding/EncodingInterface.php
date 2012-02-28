<?php

namespace MrClay\Crypt\Encoding;
use MrClay\Crypt\ByteString;

/**
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
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
