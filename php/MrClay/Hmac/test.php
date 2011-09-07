<?php

require dirname(__DIR__) . '/Rand.php';
require dirname(__DIR__) . '/Hmac.php';

header('Content-Type: text/plain');

$hmac = new MrClay_Hmac('My big secret');
$signed = $hmac->sign("Hello World!");

echo "sign('Hello World!') = ";
var_export($signed);

echo "\n\nisValid(value, salt, hash) = ";
var_export($hmac->isValid($signed));
