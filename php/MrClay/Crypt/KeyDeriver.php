<?php

namespace MrClay\Crypt;

use MrClay\Crypt\ByteString;

/**
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class KeyDeriver {

    /**
     * Number of block HMAC iterations to perform
     *
     * @var int
     */
    public $numIterations = 5000;

    /**
     * Size of desired key, in bytes
     *
     * @var int
     */
    public $keyLength = 32;

    /**
     * Algorithm to use in HMAC calculations
     *
     * @var string
     */
    public $hashAlgo = 'sha256';

    /**
     * Length of salt to generate (if not provided), in bytes
     *
     * @var int
     */
    public $saltLength = 16;

    /**
     * Create key from a password using PBKDF2 (described in RFC 2898)
     *
     * @param string $password
     *
     * @param ByteString $salt (optional)
     *
     * @return ByteString[] key, salt
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

    /**
     * Create key from a password using PBKDF2 (described in RFC 2898)
     *
     * @param string $password
     *
     * @param float $minimumSec minimum time to iterate (in seconds)
     * 
     * @param int $minimumIterations
     *
     * @return array key, salt, #iterations performed 
     */
    public function pbkdf2Timed($password, $minimumSec = .5, $minimumIterations = 5000)
    {
        $salt = ByteString::rand($this->saltLength);
        $saltBytes = $salt->getBytes();
        $hashLength = strlen(hash($this->hashAlgo, null, true));
        $neededBlocks = ceil($this->keyLength / $hashLength);
        $key = '';
        $start = microtime(true);
        $endTime = $start + ($minimumSec / $neededBlocks);
        
        $iterationsPerformed = 0;
        
        for ($blockNum = 1; $blockNum <= $neededBlocks; $blockNum++) {
            // Initial hash for this block
            $iteratedBlock = $block = hash_hmac($this->hashAlgo, $saltBytes . pack('N', $blockNum), $password, true);
            // Perform block iterations
            if ($blockNum == 1) {
                $iterationsToPerform = $minimumIterations;
                do {
                    for ($i = 1; $i < $iterationsToPerform; $i++) {
                        // XOR each iterate
                        $iteratedBlock ^= ($block = hash_hmac($this->hashAlgo, $block, $password, true));
                    }
                    // we lose count of one every time through loop
                    $iterationsPerformed += $iterationsToPerform - 1;
                } while (microtime(true) < $endTime);
                
                // ...add it back
                $iterationsPerformed++;
                
            } else {
                for ($i = 1; $i < $iterationsPerformed; $i++) {
                    // XOR each iterate
                    $iteratedBlock ^= ($block = hash_hmac($this->hashAlgo, $block, $password, true));
                }
            }
            
            $key .= $iteratedBlock;
        }
        
        $key = substr($key, 0, $this->keyLength);
        return array(new ByteString($key), $salt, $iterationsPerformed);
    }
}