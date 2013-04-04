<?php

namespace MrClay\Crypt\Cipher;

use MrClay\Crypt\ByteString;
use MrClay\Crypt\KeyDeriver;

/**
 * Block cipher base class, a more OO approach to the Mcrypt lib.
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
abstract class Base {

    /**
     * @var resource
     */
    protected $td;

    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var ByteString
     */
    protected $iv;

    /**
     * @var ByteString
     */
    protected $key;

    /**
     * @throws \InvalidArgumentException
     * @param \MrClay\Crypt\ByteString $key
     * @return Base
     */
    public function setKey(ByteString $key)
    {
        if ($key->getSize() !== $this->getKeySize()) {
            throw new \InvalidArgumentException('Key size must match maxKeySize()');
        }
        $this->key = $key;
        return $this;
    }

    /**
     * Turn a password into key of the appropriate size for this cipher
     *
     * @param string $password
     * @param \MrClay\Crypt\KeyDeriver|null $keyDeriver
     * @return ByteString
     */
    public function deriveKey($password, KeyDeriver $keyDeriver = null)
    {
        if (! $keyDeriver) {
            $keyDeriver = new KeyDeriver();
        }
        $keyDeriver->keySize = $this->getKeySize();
        list($key) = $keyDeriver->pbkdf2($password);
        return $key;
    }

    /**
     * Required key size
     *
     * @return int in bytes
     */
    public function getKeySize()
    {
        return mcrypt_enc_get_key_size($this->td);
    }

    /**
     * @return int in bytes
     */
    public function getIvSize()
    {
        return mcrypt_enc_get_iv_size($this->td);
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return strtolower(mcrypt_enc_get_modes_name($this->td));
    }

    /**
     * @return string
     */
    public function getAlgorithm()
    {
        return strtolower(mcrypt_enc_get_algorithms_name($this->td));
    }

    /**
     * @return int in bytes
     */
    public function getBlockSize()
    {
        return mcrypt_enc_get_block_size($this->td);
    }

    /**
     * @return ByteString
     */
    public function getIv()
    {
        if (! $this->iv) {
            $this->setIv();
        }
        return $this->iv;
    }

    /**
     * @throws \InvalidArgumentException
     * @param \MrClay\Crypt\ByteString|null $iv
     * @return Base
     */
    public function setIv(ByteString $iv = null)
    {
        if ($iv) {
            if ($iv->getSize() !== $this->getIvSize()) {
                throw new \InvalidArgumentException('IV size must match algorithm');
            }
        } else {
            $iv = new ByteString(mcrypt_create_iv($this->getIvSize()));
        }
        $this->iv = $iv;
        return $this;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function initialize()
    {
        if (! $this->key) {
            throw new \Exception('Key not set');
        }
        $ret = mcrypt_generic_init($this->td, $this->key->getBytes(), $this->getIv()->getBytes());
        if ($ret >= 0) {
            $this->isInitialized = true;
            return true;
        }
        return false;
    }

    /**
     * Encrypt a message, by default creating a fresh IV before. Afterwards you'll want to capture the IV
     * for storage alongside the cipherText
     *
     * @param string $plainText
     * @param bool $forceNewIv
     * @return bool|\MrClay\Crypt\ByteString the cipherText, or false on failure to initialize
     */
    public function encrypt($plainText, $forceNewIv = true)
    {
        if ($forceNewIv) {
            $this->setIv();
        }
        if (! $this->isInitialized && ! $this->initialize($forceNewIv)) {
            return false;
        }
        $ret = new ByteString(mcrypt_generic($this->td, $plainText));
        $this->isInitialized = false;
        return $ret;
    }

    /**
     * Decrypt a cipherText. You must have already set the IV to your stored IV. Note that decrypt will not "fail" on
     * an incorrect key/IV pair, you'll just get garbage, so you should store a MAC of the message and IV.
     *
     * @param \MrClay\Crypt\ByteString $cipherText
     * @return bool|string the plainText or false if the cipher fails to initialize
     */
    public function decrypt(ByteString $cipherText)
    {
        if (! $this->isInitialized && ! $this->initialize()) {
            return false;
        }
        $ret = mdecrypt_generic($this->td, $cipherText->getBytes());
        $this->isInitialized = false;
        return $ret;
    }

    public function __destruct()
    {
        if ($this->isInitialized) {
            mcrypt_generic_deinit($this->td);
        }
        mcrypt_module_close($this->td);
    }
}
