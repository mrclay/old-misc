<?php

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

header('Content-Type: text/plain');

use MrClay\Crypt\ByteString;
use MrClay\Crypt\Encoding\Base64Url;

$encoding = new Base64Url();

echo $encoding->encode(ByteString::rand(32)) . "\n\n";

echo $encoding->encode(ByteString::rand(9000)) . "\n\n";
