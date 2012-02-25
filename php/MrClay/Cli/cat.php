#!/usr/bin/php
<?php

require dirname(__DIR__) . '/Cli.php';
require __DIR__ . '/Arg.php';

$cli = new MrClay\Cli;

if (! $cli->validate() || ! $cli->moreArgs) {
    echo "USAGE: ./cat.php file ...\n";
    exit(0);
}

$out = $cli->openOutput();

foreach ($cli->getPathArgs() as $file) {
    $fp = fopen($file, 'rb');
    if ($fp) {
        stream_copy_to_stream($fp, $out);
        fclose($fp);
    }
}

$cli->closeOutput();
