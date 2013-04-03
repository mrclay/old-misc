<?php

use MrClay\Crypt\ByteString;
use MrClay\Crypt\Cipher\Rijndael256;
use MrClay\Crypt\Encoding\Base64Url;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

header('Content-Type: text/plain');

$encoding = new Base64Url();

// we must make a key of the correct size.
$cipher = new Rijndael256();
$key = ByteString::rand($cipher->getKeySize());

echo "key = " . $encoding->encode($key) . "\n\n";

$msg = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent lorem sem, viverra vitae consectetur non, eleifend eu urna.";

$cipher->setKey($key);
$cipherText = $cipher->encrypt($msg);
$iv = $cipher->getIv();

echo "IV = " . $encoding->encode($iv) . "\n\n";

echo "cipherText = " . $encoding->encode($cipherText) . "\n\n";

$cipher = new Rijndael256();
$cipher->setKey($key);
$cipher->setIv($iv);
$plainText = $cipher->decrypt($cipherText);

echo "plainText = " . $plainText;