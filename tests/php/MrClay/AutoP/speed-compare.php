<?php

require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP/WordPress.php';
require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP.php';
require_once dirname(__FILE__) . '/../../../../php/MrClay/Bench.php';

$bench = new MrClay_Bench();

function compareTime($in, $test) {
    global $bench;

    $old = new MrClay_AutoP_WordPress();
    $new = new MrClay_AutoP();

    echo "Test: $test (" . strlen($in) . " bytes)\n";

    $bench->reset();
    do {
        $old->process($in);
    } while ($bench->shouldContinue());

    echo "* WordPress: {$bench->meanTime} (n={$bench->iterationsRun})\n";
    flush();
    ob_flush();

    $bench->reset();
    do {
        $new->process($in);
    } while ($bench->shouldContinue());
    
    echo "* New      : {$bench->meanTime} (n={$bench->iterationsRun})\n\n";
    flush();
    ob_flush();
}

header('Content-Type: text/plain');

$d = dir(__DIR__);
$tests = array();
while (false !== ($entry = $d->read())) {
    if (preg_match('/^([a-z\\-]+)\.in\.html$/i', $entry, $m)) {
        $tests[] = $m[1];
    }
}

$tests = array("typical-post"); // limit to a single test

$ins = array();
foreach ($tests as $test) {
    $in = file_get_contents($d->path . '/' . "{$test}.in.html");
    compareTime($in, $test);
    if ($test === 'typical-post') {
        compareTime(str_repeat($in, 100), $test . "x100");
    }
    $ins[] = $in;
}
// combine all
exit();
$in = implode("\n\n", $ins);
$test = 'combinedAll';
compareTime($in, $test);
compareTime(str_repeat($in, 100), $test . "x100");