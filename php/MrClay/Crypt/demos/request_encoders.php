<?php

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

function dump($name, $val) { echo "$name = " . var_export($val, true) . "\n\n"; }

header('Content-Type: text/plain');

$password = "Just a really bad password";

$msg = array(123, "Lorem ipsum dolor sit amet, consectetur adipiscing elit.");

dump('$msg', $msg);

echo "Testing encoding within SignedRequest\n\n";

$sr = new \MrClay\Crypt\SignedRequest($password);

$signed = $sr->encode($msg);

dump('$signed', $signed);

$verified = $sr->decode($signed);

dump('$verified', $verified);

echo "Testing encoding within EncryptedRequest\n\n";

$er = new \MrClay\Crypt\EncryptedRequest($password);

$encrypted = $er->encode($msg);

dump('$encrypted', $encrypted);

$decrypted = $er->decode($encrypted);

dump('$decrypted', $decrypted);
