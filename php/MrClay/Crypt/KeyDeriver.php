<?php

namespace MrClay\Crypt;

use MrClay\Crypt\ByteString;

/**
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class KeyDeriver {

    /**
     * Number of block HMAC iterations to perform. If using timed, this is the minimum
     *
     * @var int
     */
    public $numIterations = 5000;

    /**
     * @var float if using timed derivation, this is the minimum runtime in seconds
     */
    public $minimumTime = .5;

    /**
     * Size of desired key, in bytes
     *
     * @var int
     */
    public $keySize = 32;

    /**
     * Algorithm to use in HMAC calculations
     *
     * @var string
     */
    public $hashAlgo = 'sha256';

    /**
     * Size of salt to generate (if not provided), in bytes
     *
     * @var int
     */
    public $saltSize = 16;

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
            $salt = ByteString::rand($this->saltSize);
        }
        $saltBytes = $salt->getBytes();
        $hashSize = strlen(hash($this->hashAlgo, null, true));
        $neededBlocks = ceil($this->keySize / $hashSize);
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
        $key = substr($key, 0, $this->keySize);
        return array(new ByteString($key), $salt);
    }

    /**
     * Create key from a password using PBKDF2 but base iterations on a minimum time
     *
     * @param string $password
     *
     * @return array key, salt, #iterations performed 
     */
    public function pbkdf2Timed($password)
    {
        $salt = ByteString::rand($this->saltSize);
        $saltBytes = $salt->getBytes();
        $hashSize = strlen(hash($this->hashAlgo, null, true));
        $neededBlocks = ceil($this->keySize / $hashSize);
        $key = '';
        $startTime = microtime();
        $minTime = $this->minimumTime / $neededBlocks;
        $iterationsPerformed = 0;
        
        for ($blockNum = 1; $blockNum <= $neededBlocks; $blockNum++) {
            // Initial hash for this block
            $iteratedBlock = $block = hash_hmac($this->hashAlgo, $saltBytes . pack('N', $blockNum), $password, true);
            // Perform block iterations
            if ($blockNum == 1) {
                $iterationsToPerform = $this->numIterations;
                do {
                    for ($i = 1; $i < $iterationsToPerform; $i++) {
                        // XOR each iterate
                        $iteratedBlock ^= ($block = hash_hmac($this->hashAlgo, $block, $password, true));
                    }
                    // we lose count of one every time through loop
                    $iterationsPerformed += $iterationsToPerform - 1;
                    
                    // keep doing 250 more until we run out of time
                    $iterationsToPerform = 250;
                    
                } while ($this->microtimeDiff($startTime) < $minTime);
                
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
        
        $key = substr($key, 0, $this->keySize);
        return array(new ByteString($key), $salt, $iterationsPerformed);
    }

    /**
     * @param string $startMicrotime
     * @return float
     */
    protected function microtimeDiff($startMicrotime) {
        $end = microtime();
        list($startUsec, $startSec) = explode(" ", $startMicrotime);
        list($endUsec, $endSec) = explode(" ", $end);
        $diffSec = intval($endSec) - intval($startSec);
        $diffUsec = floatval($endUsec) - floatval($startUsec);
        return floatval($diffSec) + $diffUsec;
    }
}