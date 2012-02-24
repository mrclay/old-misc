#!/usr/bin/php
<?php

require dirname(__DIR__) . '/CliArgs.php';

$ca = new MrClay\CliArgs;
$ca->addArgument('a', array('canHaveValue' => 1));
$ca->addArgument('i', array('mustHaveValue' => 1));

if (! $ca->validate()) {
    echo $ca->getErrors();
    echo "USAGE: ./test.php [-a [OPTIONAL STRING]] INFILES\n";
    exit(0);
}

if ($ca->values['o']) {
    // we know output is written to file $ca->values['o'] so it's safe to echo
    
    echo "Writing to " . $ca->values['o'] . "\n";
}

// identical handling of streams and files
$in = $ca->openInput();
$out = $ca->openOutput();

fwrite($out, var_export(array('values' => $ca->values, 'debug' => $ca->debug), 1) . "\n");

$ca->closeInput();
$ca->closeOutput();
