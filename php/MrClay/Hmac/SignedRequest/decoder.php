<?php

require dirname(__DIR__) . '/../Rand.php';
require dirname(__DIR__) . '/../Hmac.php';
require dirname(__DIR__) . '/../Hmac/SignedRequest.php';

function h($txt) { return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8'); }

header('Content-Type: text/plain');

$sr = new MrClay_Hmac_SignedRequest('My big secret!');
list($isValid, $json) = $sr->receive();

if ($isValid) {
    $val = json_decode($json, true);
    // echo back
    var_export($val);
} else {
    echo "bad request!";
}