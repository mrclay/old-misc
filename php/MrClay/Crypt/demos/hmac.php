<?php

use MrClay\Crypt\Hmac;
use MrClay\Crypt\SignedRequest;
use MrClay\Crypt\ByteString;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

header('Content-Type: text/plain');

$key = ByteString::rand(32);

$hmac = new Hmac($key);

$signed = $hmac->sign('My important message!');

echo $signed->encode() . "\n\n";

var_export($hmac->isValid($signed));
echo "\n\n";

$signedRequest = new SignedRequest(new Hmac($key));
$value = array(
    'Hello' => array('world!', 42)
);

$signed = $signedRequest->encode($value);

list($isValid, $value) = $signedRequest->decode($signed);

var_export($value);
