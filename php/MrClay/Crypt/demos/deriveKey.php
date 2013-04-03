<?php

use MrClay\Crypt\ByteString;
use MrClay\Crypt\Encoding\Base64Url;
use MrClay\Crypt\KeyDeriver;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

header('Content-Type: text/plain');

$deriver = new KeyDeriver();
$encoding = new Base64Url();

$password = 'password1';

list($key, $salt) = $deriver->pbkdf2($password);
echo "key = " . $encoding->encode($key) . "\n";
echo "salt = " . $encoding->encode($salt) . "\n\n\n";

// hash password for ~.25 seconds
list($key, $salt, $iterations) = $deriver->pbkdf2Timed($password, 0.25);
echo "key = " . $encoding->encode($key) . "\n";
echo "salt = " . $encoding->encode($salt) . "\n";
echo "iterations = " . $iterations . "\n";

// verify hash
$deriver->numIterations = $iterations;
list($verifyKey) = $deriver->pbkdf2($password, $salt);

echo "verified = " . (int)$key->equals($verifyKey);
