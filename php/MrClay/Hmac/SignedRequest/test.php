<?php

require dirname(__DIR__) . '/../Rand.php';
require dirname(__DIR__) . '/../Hmac.php';
require dirname(__DIR__) . '/../Hmac/SignedRequest.php';

header('Content-Type: text/plain');

$sr = new MrClay_Hmac_SignedRequest('My big secret!');
$post = $sr->generatePost(null);

echo "generatePost() => ";
var_export($post);

list($isValid, $value) = $sr->receive($post);

echo "\n\nisValid = ";
var_export($isValid);
echo "\nvalue = ";
var_export($value);
