<?php

require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP/WordPress.php';
require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP.php';

if (isset($_GET['test']) && preg_match('/^[a-z\\-]+$/i', $_GET['test'])) {
    $class = isset($_GET['new']) ? 'MrClay_AutoP' : 'MrClay_AutoP_WordPress';

    $file = __DIR__ . '/' . $_GET['test'] . '.in.html';
    if (! is_file($file)) {
        die();
    }
    $in = file_get_contents($file);

    $obj = new $class();

    $peak0 = memory_get_peak_usage();
    
    $obj->process($in);

    $peak1 = memory_get_peak_usage();

    header('Content-Type: text/javascript');
    echo json_encode(array(
        'bytesIn' => strlen($in),
        'peakUsage-before' => $peak0,
        'peakUsage-after' => $peak1,
    ));
    exit();
}

header('Content-Type: text/plain');

$d = dir(__DIR__);
$tests = array();
while (false !== ($entry = $d->read())) {
    if (preg_match('/^([a-z\\-]+)\.in\.html$/i', $entry, $m)) {
        $tests[] = $m[1];
    }
}

$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

foreach ($tests as $test) {
    $old = json_decode(file_get_contents("$url?test=$test"), true);
    $new = json_decode(file_get_contents("$url?test=$test&new"), true);
    echo "\n\n$test :\n";
    var_export(array(
        'old-peak-memory-raised' => ($old['peakUsage-after'] - $old['peakUsage-before']),
        'new-peak-memory-raised' => ($new['peakUsage-after'] - $new['peakUsage-before']),
    ));
}