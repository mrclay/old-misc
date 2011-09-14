<?php

require __DIR__ . '/../../Loader.php';
MrClay_Loader::register();

header('Content-Type: text/plain');

$password = "Just a really bad password";

$encoding = new MrClay\Crypt\Encoding\Base64Url();

// let's make a good string key (won't directly be used to encrypt)
$key = \MrClay\Crypt\ByteString::rand(64);
$key = $key->getBytes();

echo "key = " . base64_encode($key) . "\n\n";

$msg = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent lorem sem, viverra vitae consectetur non, eleifend eu urna.";

$encryption = new MrClay\Crypt\Encryption($key);

$storage = $encryption->encrypt($msg);

echo "encrypted = " . $storage->encode() . "\n\n";

$decoded = $encryption->decrypt($storage);

echo "original  = " . $msg . "\n";
echo "decrypted = " . $decoded;
