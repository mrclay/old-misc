<?php

namespace MrClay\Crypt;

/**
 * Simple container for a binary string.
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class ByteString {

    /**
     * @param ByteString|string $bytes
     */
    public function __construct($bytes)
    {
        if ($bytes instanceof ByteString) {
            $this->bytes = $bytes->getBytes();
        } else {
            $this->bytes = $bytes;    
        }
    }

    /**
     * Salt generated during the key derivation process (null if key was not derived)
     *
     * @var ByteString|null
     */
    public $passwordSalt;

    /**
     * @param string $password
     * @param int $length
     * @param ByteString $salt
     * @param KeyDeriver $keyDeriver
     * @return ByteString
     */
    public static function createFromPassword($password, $length = 32,
                                        ByteString $salt = null, KeyDeriver $keyDeriver = null)
    {
        if (! $keyDeriver) {
            $keyDeriver = new KeyDeriver();
        }
        $keyDeriver->keyLength = $length;
        list($key, $salt) = $keyDeriver->pbkdf2($password, $salt);
        $return = new self($key);
        $return->passwordSalt = $salt;
        return $return;
    }

    /**
     * Create a random byte string, based on drupal_random_bytes
     * 
     * @param int $count number of bytes to generate
     * @return ByteString
     */
    public static function rand($count)
    {
        return new self(self::drupalRandomBytes($count));
    }

    /**
     * Returns a string of highly randomized bytes (over the full 8-bit range).
     *
     * This function is better than simply calling mt_rand() or any other built-in
     * PHP function because it can return a long string of bytes (compared to < 4
     * bytes normally from mt_rand()) and uses the best available pseudo-random
     * source.
     *
     * @param int $count The number of characters (bytes) to return in the string.
     * @return string
     * 
     * @link https://github.com/drupal/drupal/blob/8.x/core/includes/bootstrap.inc#L1936
     * @license https://github.com/drupal/drupal/blob/8.x/core/LICENSE.txt
     */
    public static function drupalRandomBytes($count)
    {
        static $random_state, $bytes, $php_compatible;
        // Initialize on the first call. The contents of $_SERVER includes a mix of
        // user-specific and system information that varies a little with each page.
        if (!isset($random_state)) {
            $random_state = print_r($_SERVER, TRUE);
            if (function_exists('getmypid')) {
                // Further initialize with the somewhat random PHP process ID.
                $random_state .= getmypid();
            }
            $bytes = '';
        }
        if (strlen($bytes) < $count) {
            // PHP versions prior 5.3.4 experienced openssl_random_pseudo_bytes()
            // locking on Windows and rendered it unusable.
            if (!isset($php_compatible)) {
                $php_compatible = version_compare(PHP_VERSION, '5.3.4', '>=');
            }
            // /dev/urandom is available on many *nix systems and is considered the
            // best commonly available pseudo-random source.
            if ($fh = @fopen('/dev/urandom', 'rb')) {
                // PHP only performs buffered reads, so in reality it will always read
                // at least 4096 bytes. Thus, it costs nothing extra to read and store
                // that much so as to speed any additional invocations.
                $bytes .= fread($fh, max(4096, $count));
                fclose($fh);
            }
            // openssl_random_pseudo_bytes() will find entropy in a system-dependent
            // way.
            elseif ($php_compatible && function_exists('openssl_random_pseudo_bytes')) {
                $bytes .= openssl_random_pseudo_bytes($count - strlen($bytes));
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
                $random_state = hash('sha256', microtime() . mt_rand() . $random_state);
                $bytes .= hash('sha256', mt_rand() . $random_state, TRUE);
            }
        }
        $output = substr($bytes, 0, $count);
        $bytes = substr($bytes, $count);
        return $output;
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
     * Compare to another ByteString, avoiding timing attacks
     * 
     * @param ByteString $bs
     * @return bool
     */
    public function equals(ByteString $bs)
    {
        return self::compareStrings($this->bytes, $bs->getBytes());
    }

    /**
     * Compare two strings to avoid timing attacks
     *
     * C function memcmp() internally used by PHP, exits as soon as a difference
     * is found in the two buffers. That makes possible of leaking
     * timing information useful to an attacker attempting to iteratively guess
     * the unknown string (e.g. password).
     * 
     * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * @param  string $expected
     * @param  string $actual
     * @return bool
     */
    public static function compareStrings($expected, $actual)
    {
        $expected     = (string) $expected;
        $actual       = (string) $actual;
        $lenExpected  = strlen($expected);
        $lenActual    = strlen($actual);
        $len          = min($lenExpected, $lenActual);

        $result = 0;
        for ($i = 0; $i < $len; $i++) {
            $result |= ord($expected[$i]) ^ ord($actual[$i]);
        }
        $result |= $lenExpected ^ $lenActual;

        return ($result === 0);
    }

    /**
     * @var string
     */
    protected $bytes = '';
}
