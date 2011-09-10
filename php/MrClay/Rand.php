<?php

/**
 * Random char/byte generator based on PHPass
 * 
 * @link http://www.openwall.com/phpass/
 * 
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 *
 * @deprecated use MrClay\Crypt\ByteString
 */
class MrClay_Rand {
    
    /**
     * Create string of random bytes (from PHPass)
     * 
     * @param int $numBytes
     * 
     * @return string
     */
    public function getBytes($numBytes)
    {
        // generate random bytes (adapted from phpass)
        $randomState = microtime();
        if (function_exists('getmypid')) {
            $randomState .= getmypid();
        }
        $bytes = '';
        if (@is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
            $bytes = fread($fh, $numBytes);
            fclose($fh);
        }
        if (strlen($bytes) < $numBytes) {
            $bytes = '';
            for ($i = 0; $i < $numBytes; $i += 16) {
                $randomState = md5(microtime() . $randomState . mt_rand(0, mt_getrandmax()));
                $bytes .= pack('H*', md5($randomState));
            }
            $bytes = substr($bytes, 0, $numBytes);
        }
        
        return $bytes;
    }
    
    /**
     * Create string of random URL-safe chars
     * 
     * @link http://en.wikipedia.org/wiki/Base64#URL_applications
     * 
     * @param int $numChars
     * 
     * @return string
     */
    public function getUrlSafeChars($numChars)
    {
        $bytes = $this->getBytes($numChars);
        return substr(rtrim(strtr(base64_encode($bytes), '+/', '-_'), '='), 0, $numChars);
    }
}