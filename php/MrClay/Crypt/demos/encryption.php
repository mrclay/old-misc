<?php

use MrClay\Crypt\ByteString;
use MrClay\Crypt\Encryption;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

header('Content-Type: text/plain');

$key = ByteString::rand(32);

$encoding = new MrClay\Crypt\Encoding\Base64Url();

echo "key = " . $encoding->encode($key) . "\n\n";

$msg = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent lorem sem, viverra vitae consectetur non, eleifend eu urna.";

$encryption = new MrClay\Crypt\Encryption($key);

$storage = $encryption->encrypt($msg);

echo "encrypted = " . $storage->encode() . "\n\n";

$decoded = $encryption->decrypt($storage);

echo "original  = " . $msg . "\n";
echo "decrypted = " . $decoded;
