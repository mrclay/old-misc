#!/usr/bin/php
<?php

/*

Basic version of Unix "hd"

Examples:

echo "Hello World" | ./hd.php
echo "Hello World" | ./hd.php -n 5
echo "Hello World" | ./hd.php -o dump.txt
./hd.php -i dump.txt > dump2.txt
./hd.php -i noExiste  # fails validation due to file not readable

*/

require dirname(__DIR__) . '/CliArgs.php';

$ca = new MrClay\CliArgs;
$ca->addArgument('i', array('STDIN' => 1));
$ca->addArgument('o', array('STDOUT' => 1));
$ca->addArgument('n', array('mustHaveValue' => 1)); // bytes to copy

if (! $ca->validate()) {
    echo $ca->getErrors();
    echo "USAGE: ./hd.php [-n NUMBYTES] [-i INFILE] [-o OUTFILE]\n";
    exit(0);
}

    
if ($ca->values['o']) {
    // we know output is written to file $ca->values['o'] so it's safe to echo
    
    if ($ca->values['i']) {
        // we know input is read from file $ca->values['i']
        echo "We will read bytes from " . $ca->values['i'] . "\n";
    }
    
    echo "We will save the hexdump to " . $ca->values['o'] . "\n";
}

// identical handling of streams and files
$in = $ca->openInput();
$out = $ca->openOutput();

$maxLength = is_string($ca->values['n'])
    ? (int)$ca->values['n']
    : -1;

$i = 0;
while ((false !== ($char = fgetc($in)))) {
    $hex = sprintf('%02x', ord($char));
    fwrite($out,  "$hex ");
    $i++;
    if ($i == $maxLength) {
        break;
    }
}
fwrite($out, "\n");

if ($ca->values['o']) {
    echo "Processed $i bytes.\n";
}

$ca->closeInput();
$ca->closeOutput();
