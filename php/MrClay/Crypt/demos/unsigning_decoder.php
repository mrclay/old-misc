<?php

use MrClay\Crypt\SignedRequest;
use MrClay\Crypt\Hmac;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

function dump($name, $val) { echo "$name = " . var_export($val, true) . "\n\n"; }
function h($txt) { return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8'); }

$key = (require __DIR__ . '/_key.php');

header('Content-Type: text/plain');

$er = new SignedRequest(new Hmac($key));

list($isValid, $val) = $er->receive();

if ($isValid) {
    dump('verified value', $val);
} else {
    echo "Bad request.";
}
