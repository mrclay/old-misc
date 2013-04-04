<?php

namespace MrClay\Crypt;

use MrClay\Crypt\Encoding\Base64Url;
use MrClay\Crypt\KeyDeriver;

class PasswordHasher {

    /**
     * @var KeyDeriver
     */
    protected $keyDeriver;

    /**
     * @param KeyDeriver $keyDeriver
     */
    public function __construct(KeyDeriver $keyDeriver = null)
    {
        if (! $keyDeriver) {
            $keyDeriver = new KeyDeriver();
        }
        $this->keyDeriver = $keyDeriver;
    }

    /**
     * @param string $password
     * @param bool $timed
     * @return string
     */
    public function hashPassword($password, $timed = false)
    {
        if ($timed) {
            list($key, $salt, $iterations) = $this->keyDeriver->pbkdf2Timed($password);
        } else {
            list($key, $salt) = $this->keyDeriver->pbkdf2($password);
            $iterations = $this->keyDeriver->numIterations;
        }
        
        $enc = new Base64Url();
        return implode('.', array(
            $enc->encode($key),
            $enc->encode($salt),
            $iterations,
        ));
    }

    /**
     * @param string $password
     * @param string $hash
     * @return array [isValid (bool), # iterations in hash]
     */
    public function verifyPassword($password, $hash)
    {
        $hash = explode('.', $hash);
        if (count($hash) !== 3) {
            return array(false, 0);
        }

        $enc = new Base64Url();
        $key = $enc->decode($hash[0]);
        $salt = $enc->decode($hash[1]);
        $iterations = $hash[2];
        
        $this->keyDeriver->numIterations = $iterations;
        list($computedKey) = $this->keyDeriver->pbkdf2($password, $salt);
        
        return array($key->equals($computedKey), $iterations);
    }
}
