<?php

namespace MrClay\Crypt;

use MrClay\Crypt\ByteString;
use MrClay\Crypt\Encoding\Base64Url;

/**
 * Not implemented
 */
class PasswordHasher {
//
//    /**
//     * @var int
//     */
//    protected $stretching = 12;
//
//    /**
//     * @var int
//     */
//    protected $saltLength = 6;
//
//    /**
//     * @var string
//     */
//    protected $algoKey = 'a';
//
//    /**
//     * @var Base64Url
//     */
//    protected $encoding;
//
//    const STRETCHING_MIN = 7;
//    const STRETCHING_MAX = 30;
//
//    /**
//     * @return array
//     */
//    public function getAlgoMap()
//    {
//        return array(
//            'a' => 'sha256',
//            'b' => 'sha512',
//            'c' => 'ripemd160',
//            'd' => 'whirlpool',
//        );
//    }
//
//    /**
//     * @throws \InvalidArgumentException
//     * @param int $log2Count The log2 number of iterations for password stretching.
//     * @return PhPass
//     */
//    public function setStretching($log2Count)
//    {
//        $log2Count = (int) $log2Count;
//        if ($log2Count < self::STRETCHING_MIN || $log2Count > self::STRETCHING_MAX) {
//            throw new \InvalidArgumentException('Stretching value out of range');
//        }
//        $this->stretching = $log2Count;
//        return $this;
//    }
//
//    /**
//     * @throws \InvalidArgumentException
//     * @param int $numBytes
//     * @return PasswordHasher
//     */
//    public function setSaltLength($numBytes)
//    {
//        $numBytes = (int) $numBytes;
//        if ($numBytes < 3 || $numBytes > 12) {
//            throw new \InvalidArgumentException('Salt length must be within 3 and 12 bytes');
//        }
//        $this->saltLength = $numBytes;
//        return $this;
//    }
//
//    public function __construct()
//    {
//        $this->encoding = new Base64Url();
//    }
//
//    public function generateHash($password)
//    {
//        $salt = ByteString::rand($this->saltLength);
//        $hash = $this->digest($password, $salt, $this->algoKey, $this->stretching);
//
//    }
//
//    protected function digest($password, $salt, $algoKey, $stretchingKey)
//    {
//        $map = $this->getAlgoMap();
//        if (! isset($map[$algoKey])) {
//            return false;
//        }
//        $algo = $map[$algoKey];
//
//
//
//        $iterations = 1 << $stretching;
//        $hash = hash($algo, $salt . $password, true);
//        do {
//            $hash = hash($algo, $hash . $password, true);
//        } while (--$iterations);
//        return hash;
//    }
}
