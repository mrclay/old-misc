<?php

require '../TimeZone.php';

header('Content-Type: text/plain');

// parse a date string from Eastern Standard Time (no DST)
$est = new MrClay_TimeZone(-5);

$time = $est->strtotime('2007-06-01 08:00:00');
$time2 = $est->mktime(8, 0, 0, 6, 1, 2007);

echo ($time == $time2 ? 'PASS: ' : '!FAIL: ')
, "strtotime and mktime produce same time\n";

// display in New York time
$ny = new MrClay_TimeZone('America/New_York');
$nyDate = $ny->date('Y-m-d H:i', $time);

echo ($nyDate === '2007-06-01 09:00' ? 'PASS: ' : '!FAIL: ')
, $nyDate , "\n";

$nyDST = $ny->date('I', $time);

echo ($nyDST === '1' ? 'PASS: ' : '!FAIL: ')
, "Date is DST in New York Time\n";

$getDate = $ny->getdate($time);
$passed = (
    $getDate['seconds'] == 0
    && $getDate['minutes'] == 0
    && $getDate['hours'] == 9
    && $getDate['mday'] == 1
    && $getDate['mon'] == 6
    && $getDate['year'] == 2007
);
echo ($passed ? 'PASS: ' : '!FAIL: ')
, "getdate output correct\n";

$localtime = $ny->localtime($time, true);
$passed = (
    $localtime['tm_sec'] == 0
    && $localtime['tm_min'] == 0
    && $localtime['tm_hour'] == 9
    && $localtime['tm_mday'] == 1
    && $localtime['tm_mon'] == 5
    && $localtime['tm_year'] == 107
);

$ftime = $ny->strftime('%F %T', $time);
echo ($ftime == '2007-06-01 09:00:00' ? 'PASS: ' : '!FAIL: ')
, "strftime output correct\n";

