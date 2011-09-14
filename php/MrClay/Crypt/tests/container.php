<?php

require __DIR__ . '/../../Loader.php';
MrClay_Loader::register();

header('Content-Type: text/plain');

$cont = new MrClay\Crypt\Container();

$cont[] = MrClay\Crypt\ByteString::rand(32);
$cont[] = MrClay\Crypt\ByteString::rand(16);
$cont[] = MrClay\Crypt\ByteString::rand(32);

$encoding = new MrClay\Crypt\Encoding\Base64Url();

var_export($cont->getSizes());
echo "\n\nThese should match:\n";

$encodedCont = $cont->encode($encoding);

echo $encodedCont . "\n";

$cont = \MrClay\Crypt\Container::decode($encoding, $encodedCont);

echo $cont->encode($encoding) . "\n";

$bin = $cont->toBinary();

$cont = \MrClay\Crypt\Container::fromBinary($bin);

echo $cont->encode();




