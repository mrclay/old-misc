<?php

namespace MrClay\Crypt;

/**
 * Simple container for a binary string.
 */
class ByteString {

    /**
     * @param string $bytes
     */
    public function __construct($bytes)
    {
        $this->bytes = $bytes;
    }

    /**
     * Create a random byte string
     * @param $size
     * @return ByteString
     *
     * @link http://www.openwall.com/phpass/
     */
    public static function rand($size)
    {
        if (function_exists('mcrypt_create_iv')) {
            $bytes = mcrypt_create_iv($size, MCRYPT_DEV_RANDOM);
        } elseif (@is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
            $bytes = fread($fh, $size);
            fclose($fh);
        } else {
            // as good as we can do, algorithm from PHPass
            $randomState = microtime();
            if (function_exists('getmypid')) {
                $randomState .= getmypid();
            }
            $bytes = '';
            for ($i = 0; $i < $size; $i += 16) {
                $randomState = md5(microtime() . $randomState . mt_rand(0, mt_getrandmax()));
                $bytes .= pack('H*', md5($randomState));
            }
            $bytes = substr($bytes, 0, $size);
        }
        return new self($bytes);
    }

    /**
     * @return string
     */
    public function getBytes()
    {
        return $this->bytes;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return strlen($this->bytes);
    }

    /**
     * @param ByteString $bs
     * @return bool
     */
    public function equals(ByteString $bs)
    {
        return ($this->bytes === $bs->getBytes());
    }

    /**
     * @var string
     */
    protected $bytes = '';
}
