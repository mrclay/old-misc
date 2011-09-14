<?php

namespace MrClay\Crypt\Cipher;

use MrClay\Crypt\ByteString;

/**
 * The AES-256 cipher in counter mode
 *
 * @link http://www.daemonology.net/blog/2009-06-11-cryptographic-right-answers.html
 */
class Rijndael256 extends Base {

    public function __construct()
    {
        $this->td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', 'ctr', '');
    }
}
