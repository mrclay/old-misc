<?php

use MrClay\Crypt\ByteString;
use MrClay\Crypt\Container;
use MrClay\Crypt\Encoding\Base64Url;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

header('Content-Type: text/plain');

$cont = new Container();

$cont->push(ByteString::rand(32));
$cont->push(ByteString::rand(16));
$cont->push(ByteString::rand(32));

var_export($cont->getSizes());
echo "\n\nThese should match:\n";

$encoding = new Base64Url();

$encodedCont = $cont->encode($encoding);

echo $encodedCont . "\n";

$cont = \MrClay\Crypt\Container::decode($encoding, $encodedCont);

echo $cont->encode($encoding) . "\n";

$bin = $cont->toBinary();

$cont = \MrClay\Crypt\Container::fromBinary($bin);

echo $cont->encode();
