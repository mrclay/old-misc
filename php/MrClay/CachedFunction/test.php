<?php

// this script executable at:
// http://mrclay.org/code_trunk/php/MrClay/CachedFunction/test.php

ini_set('display_errors', 1);
require dirname(__FILE__) .  '/../CachedFunction.php';
require dirname(__FILE__) .  '/../CachedFunction/Cache/File.php';

function slowTime()
{
    $delaySeconds = 4;
    usleep($delaySeconds * 1000000);
    return time();
}

function validateTime($value)
{
    return true;
}

$func = new MrClay_CachedFunction(
    ($_SERVER['DOCUMENT_ROOT'] . '/../sessions')
    ,'slowTime'
    ,'validateTime'
);
$func->maxWait = 8; // if lock older than this, consider it abandoned
$func->cacheUnusableAge = 20; // demo only. usually you want this very high
$func->cacheStaleAge = 6;
$func->queueCalls = true; // we'll do work after connection close

ob_start();
?>
<h1>Test of <a href="http://code.google.com/p/mrclay/source/browse/trunk/php/MrClay/CachedFunction.php">MrClay_CachedFunction</a></h1>

<p>Only the first process to find the cache stale is allowed to refresh the cache 
(using a lock). While the refresh takes place, all other processes receive the
last stale value.</p>

<p>In this configuration, the function call is also queued until after output is
sent to the client. This allows the refreshing client to receive immediate (stale) 
output.</p>

<pre>$func-&gt;getReturn() = <?php 
    echo htmlspecialchars(var_export($func->getReturn(), 1));
    ?></pre>

<?
header('Cache-Control: no-cache');
// required to close the browser connection after sending output
header('Content-Length: ' . ob_get_length());
header('Connection: close');
ob_end_flush();
flush();

// run the queue of function calls
$func->runQueue();
