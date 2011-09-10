<?php

require __DIR__ . '/../../Loader.php';
MrClay_Loader::register();

header('Content-Type: text/plain');

$hmac = new MrClay\Crypt\Hmac('password1');

$signed = $hmac->sign('My important message!');

echo $signed->encode() . "\n\n";

var_export($hmac->isValid($signed));
echo "\n\n";

$signedRequest = new MrClay\Crypt\SignedRequest('password1');
$value = array(
    'Hello' => array('world!', 42)
);

$signed = $signedRequest->encode($value);

list($isValid, $value) = $signedRequest->decode($signed);

var_export($value);
