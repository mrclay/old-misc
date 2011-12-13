<?php

header('Content-Type: text/plain; charset=utf-8');

use java\lang\StringBuilder;

require dirname(__DIR__) . '/StringBuilder.php';

$sb = new StringBuilder("ѶIñtërnâtiônàlizætiøn");

echo $sb->getBytes() . "\n";
$ern = new StringBuilder("ërn");
echo $sb->indexOf($ern) . "\n";
echo $sb->charAt(2) . "\n";
echo var_export($sb->codePointAt(0), 1) . "\n";
echo $sb->toUpperCase()->getBytes() . "\n";

