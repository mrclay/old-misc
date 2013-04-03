<?php

use MrClay\Crypt\Encoding\Base64Url;
use MrClay\Crypt\EncryptedRequest;
use MrClay\Crypt\Encryption;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

function dump($name, $val) { echo "$name = " . var_export($val, true) . "\n\n"; }
function h($txt) { return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8'); }

$encoding = new Base64Url();
$key = $encoding->decode('TPwFGDfoaw-cLulL_WCE4RUHATWz9AOx3mnmjxv-5Ls');

header('Content-Type: text/plain');

$er = new EncryptedRequest(new Encryption($key));

list($isValid, $val) = $er->receive();

if ($isValid) {
    dump('decrypted value', $val);
} else {
    echo "Bad request.";
}
