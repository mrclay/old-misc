<?php
/**
 * @deprecated use MrClay_Hmac
 */


/** 
 * Simple functions to hash, sign and verify signed content using randomly
 * salted hashes.
 *
 * MD5 collisions can be engineered with the use of rainbow tables, but when 
 * random salts are introduced, this becomes ineffective.
 * 
 * @link http://en.wikipedia.org/wiki/Rainbow_table#Defense_against_rainbow_tables
 * 
 * @deprecated use MrClay_Hmac
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_HashUtils {
    
    public static $saltLength = 9;
    
    /**
     * Generate a fixed-length hash with a random salt. 
     *
     * The last 40 bytes are hex output of sha1($salt . $content). The first 
     * bytes are salt with the length specified by MrClay_HashUtils::$saltLength.
     * By default this will return 49 ASCII characters.
     *
     * If you supply non-ASCII salt bytes, be prepared to transmit and store them.
     *
     * Use this for truly secure password hashing, as suggested in
     * @link http://phpsec.org/articles/2005/password-hashing.html
     *
     * <code>
     * // password storage (by default VARCHAR(49))
     * $passwordColumn = MrClay_HashUtils::getSaltedHash($usersPassword);
     *
     * // password verification
     * $isValid = MrClay_HashUtils::verifyHash($passwordColumn, $givenPassword);
     * </code>
     *
     * @param string $content
     *
     * @param string $salt (optional) Generally you should let this function
     * generate an ASCII salt. if you supply a salt, it will be padded with 
     * random characters to the length MrClay_HashUtils::$saltLength.
     * 
     * @return string
     */
    public static function getSaltedHash($content, $salt = '')
    {
        do {
            $salt .= self::getRandomAlphaNumerics();
        } while (strlen($salt) < self::$saltLength);
        $salt = substr($salt, 0, self::$saltLength);
        return $salt . sha1($salt . $content);
    }
    
    /**
     * Was $hash generated from $content?
     *
     * @see MrClay_HashUtils::getSaltedHash
     *
     * @param string $hash output of hash()
     * 
     * @param string $content
     * 
     * @return bool 
     */
    public static function verifyHash($hash, $content)
    {
        $salt = substr($hash, 0, self::$saltLength);
        return ($hash == ($salt . sha1($salt . $content)));
    }
    
    /**
     * Append to given content a salted hash of the content and a secret key
     *
     * @param string $content
     * 
     * @param string $secretKey
     * 
     * @return $string
     */
    public static function signContent($content, $secretKey)
    {
        return $content . self::getSaltedHash($content . $secretKey);
    }
    
    /**
     * Return original content from signed content
     * 
     * @param string $signedContent
     * 
     * @param string $secretKey string used in signContent()
     * 
     * @return mixed string on success, false if signature is invalid
     */
    public static function getContent($signedContent, $secretKey)
    {
        $hashLength = self::$saltLength + 40;
        if (strlen($signedContent) < $hashLength) {
            return false;
        }
        $hash = substr($signedContent, -$hashLength);
        $content = substr($signedContent, 0, strlen($signedContent) - $hashLength);
        return self::verifyHash($hash, $content . $secretKey)
            ? $content
            : false;
    }
    
    /**
     * Get random alphanumeric characters
     *
     * By returning binary from SHA1 and encoding it as base 64, the returned
     * value will be more densely packed than hex output, therefore safer to use
     * in a salt of shorter length than 40 bytes.
     * 
     * @return string 
     */
    public static function getRandomAlphaNumerics()
    {
        $ret = base64_encode(sha1(uniqid(mt_rand(), true), true));
        return preg_replace('/[^a-zA-Z\\d]/', '', $ret);
    }
}
