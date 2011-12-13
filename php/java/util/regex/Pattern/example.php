<?php

header('Content-Type: text/plain; charset=utf-8');

use java\util\regex\Pattern;
use java\lang\StringBuilder;

require dirname(dirname(dirname(__DIR__))) . '/lang/StringBuilder.php';
require dirname(__DIR__) . '/Pattern.php';

foreach (array(
    0 => '/abc (?:def)/',
    1 => '/abc(?: (def))/',
    2 => '/a(bc) (def)/',
    3 => '/(abc(def(gh)))/',
    ) as $numGroups => $regex) {
    $pattern = Pattern::compile(new StringBuilder($regex));
    echo var_export($pattern->capturingGroupCount(), 1) . "\n";
}

$input = new StringBuilder("boo:and:foo");
foreach (array(
    array('/:/', 2),
    array('/:/', 5),
    array('/:/', -2),
    array('/o/', 5),
    array('/o/', -2),
    array('/o/', 0),
) as $test) {
    $pattern = Pattern::compile(new StringBuilder($test[0]));
    var_export($pattern->split($input, $test[1]));
}