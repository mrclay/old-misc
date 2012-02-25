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
require dirname(__DIR__) . '/Cli.php';
require __DIR__ . '/Arg.php';

$cli = new MrClay\Cli;
$cli->addOptionalArg('i')->useAsInfile();
$cli->addOptionalArg('o')->useAsOutfile();
$cli->addOptionalArg('n')->mustHaveValue();

if (! $cli->validate()) {
    echo $cli->getErrorReport();
    echo "USAGE: ./hd.php [-n NUMBYTES] [-i INFILE] [-o OUTFILE]\n";
    echo "EXAMPLE: echo \"Hello World\" | ./hd.php\n";
    exit(0);
}

    
if ($cli->values['o']) {
    // we know output is written to file $ca->values['o'] so it's safe to echo
    
    if ($cli->values['i']) {
        // we know input is read from file $ca->values['i']
        echo "We will read bytes from " . $cli->values['i'] . "\n";
    }
    
    echo "We will save the hexdump to " . $cli->values['o'] . "\n";
}

// identical handling of streams and files
$in = $cli->openInput();
$out = $cli->openOutput();

$maxLength = is_string($cli->values['n'])
    ? (int)$cli->values['n']
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

if ($cli->values['o']) {
    echo "Processed $i bytes.\n";
}

$cli->closeInput();
$cli->closeOutput();
