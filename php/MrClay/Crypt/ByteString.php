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
     * Create a random byte string, based on drupal_random_bytes (and phpass)
     * @param int $count number of bytes to generate
     * @return ByteString
     *
     * @link http://drupalcode.org/project/drupal.git/blob/refs/heads/7.x:/includes/bootstrap.inc#l1845
     */
    public static function rand($count)
    {
        static $randomState, $bytes;
        // Initialize on the first call.
        if (! isset($randomState)) {
            $randomState = print_r($_SERVER, true);
            if (function_exists('getmypid')) {
                // Further initialize with the somewhat random PHP process ID.
                $randomState .= getmypid();
            }
            $bytes = '';
        }
        if (strlen($bytes) < $count) {
            // /dev/urandom is available on many *nix systems and is considered the
            // best commonly available pseudo-random source.
            if (@is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
                // PHP only performs buffered reads, so in reality it will always read
                // at least 4096 bytes. Thus, it costs nothing extra to read and store
                // that much so as to speed any additional invocations.
                while (strlen($bytes) < $count) {
                    $bytes .= fread($fh, max(4096, $count));
                }
                fclose($fh);
            }
            // If /dev/urandom is not available or returns no bytes, this loop will
            // generate a good set of pseudo-random bytes on any system.
            // Note that it may be important that our $random_state is passed
            // through hash() prior to being rolled into $output, that the two hash()
            // invocations are different, and that the extra input into the first one -
            // the microtime() - is prepended rather than appended. This is to avoid
            // directly leaking $random_state via the $output stream, which could
            // allow for trivial prediction of further "random" numbers.
            while (strlen($bytes) < $count) {
                $randomState = hash('sha256', microtime() . mt_rand() . $randomState);
                $bytes .= hash('sha256', mt_rand() . $randomState, true);
            }
        }
        $output = substr($bytes, 0, $count);
        $bytes = substr($bytes, $count);
        return new self($output);
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
