<?php

namespace MrClay\Crypt;

use MrClay\Crypt\ByteString;

class KeyDeriver {

    public $numIterations = 5000;

    public $keyLength = 32;

    public $hashAlgo = 'sha256';

    public $saltLength = 16;

    /**
     * Create key from a password using PBKDF2 (described in RFC 2898)
     *
     * @param string $password
     * @param ByteString $salt (optional)
     * @return array [key, salt]
     *
     * @author Andrew Johnson
     * @link http://www.itnewb.com/v/Encrypting-Passwords-with-PHP-for-Storage-Using-the-RSA-PBKDF2-Standard
     */
    public function pbkdf2($password, ByteString $salt = null)
    {
        if (! $salt) {
            $salt = ByteString::rand($this->saltLength);
        }
        $saltBytes = $salt->getBytes();
        $hashLength = strlen(hash($this->hashAlgo, null, true));
        $neededBlocks = ceil($this->keyLength / $hashLength);
        $key = '';
        for ($blockNum = 1; $blockNum <= $neededBlocks; $blockNum++) {
            // Initial hash for this block
            $iteratedBlock = $block = hash_hmac($this->hashAlgo, $saltBytes . pack('N', $blockNum), $password, true);
            // Perform block iterations
            for ($i = 1; $i < $this->numIterations; $i++) {
                // XOR each iterate
                $iteratedBlock ^= ($block = hash_hmac($this->hashAlgo, $block, $password, true));
            }
            $key .= $iteratedBlock;
        }
        $key = substr($key, 0, $this->keyLength);
        return array(new ByteString($key), $salt);
    }
}