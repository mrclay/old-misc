<?php

require '../TimeZone.php';

header('Content-Type: text/plain');

// parse a date string from Eastern Standard Time (no DST)
$est = new MrClay_TimeZone(-5);
$time = $est->strtotime('2007-06-01 08:00:00');

// display in New York time
$ny = new MrClay_TimeZone('America/New_York');
$nyDate = $ny->date('Y-m-d H:i', $time);

echo ($nyDate === '2007-06-01 09:00' ? 'PASS: ' : '!FAIL: ')
    , $nyDate , "\n";

$nyDST = $ny->date('I', $time);

echo ($nyDST === '1' ? 'PASS: ' : '!FAIL: ')
    , "Date is DST in New York Time\n";
