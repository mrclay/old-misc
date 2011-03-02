<?php

require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP/WordPress.php';
require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP.php';

function compareTime($in, $test) {
    $autopWP = new MrClay_AutoP_WordPress();
    $autop = new MrClay_AutoP();

    $time0 = microtime(true);
    $autopWP->process($in);
    $time1 = microtime(true);
    $autop->process($in);
    $time2 = microtime(true);

    return array(
        'test' => $test,
        'bytes' => strlen($in),
        'time-old' => $time1 - $time0,
        'time-new' => $time2 - $time1,
    );
}

header('Content-Type: text/plain');

$d = dir(__DIR__);
$tests = array();
while (false !== ($entry = $d->read())) {
    if (preg_match('/^([a-z\\-]+)\.in\.html$/i', $entry, $m)) {
        $tests[] = $m[1];
    }
}

//$tests = array("5"); // limit to a single test

$ins = array();
foreach ($tests as $test) {
    $in = file_get_contents($d->path . '/' . "{$test}.in.html");
    var_export(compareTime($in, $test));
    //var_export(compareTime(str_repeat($in, 100), $test . "x100"));
    $ins[] = $in;
}
// combine all
$in = implode("\n\n", $ins);
$test = 'combinedAll';
var_export(compareTime($in, $test));
var_export(compareTime(str_repeat($in, 100), $test . "x100"));