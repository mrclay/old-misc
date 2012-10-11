<?php

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

function dump($name, $val) { echo "$name = " . var_export($val, true) . "\n\n"; }
function h($txt) { return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8'); }

$password = "Just a really bad password";

header('Content-Type: text/plain');

$er = new \MrClay\Crypt\SignedRequest($password);

list($isValid, $val) = $er->receive();

if ($isValid) {
    dump('verified value', $val);
} else {
    echo "Bad request.";
}
