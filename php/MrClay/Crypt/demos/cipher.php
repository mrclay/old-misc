<?php

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

header('Content-Type: text/plain');

$password = "A really bad password!";

$encoding = new MrClay\Crypt\Encoding\Base64Url();

// we must make a key of the correct size. Easiest to let the cipher derive it.
$cipher = new MrClay\Crypt\Cipher\Rijndael256();
$key = $cipher->deriveKey($password);

echo "key = " . $encoding->encode($key) . "\n\n";

$msg = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent lorem sem, viverra vitae consectetur non, eleifend eu urna.";

$cipher->setKey($key);
$cipherText = $cipher->encrypt($msg);
$iv = $cipher->getIv();

echo "IV = " . $encoding->encode($iv) . "\n\n";

echo "cipherText = " . $encoding->encode($cipherText) . "\n\n";

$cipher = new MrClay\Crypt\Cipher\Rijndael256();
$cipher->setKey($key);
$cipher->setIv($iv);
$plainText = $cipher->decrypt($cipherText);

echo "plainText = " . $plainText;