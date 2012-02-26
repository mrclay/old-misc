#!/usr/bin/php
<?php
/*

Basic version of Unix "hexdump"

Examples:

echo "Hello World" | ./hexdump.php
echo "Hello World" | ./hexdump.php -n 5
echo "Hello World" | ./hexdump.php -o dump.txt
./hexdump.php -i dump.txt > dump2.txt
./hexdump.php -i noExiste  # fails validation due to file not readable

*/
require dirname(__DIR__) . '/Cli.php';
require __DIR__ . '/Arg.php';

$cli = new MrClay\Cli;
$cli->addOptionalArg('i')->useAsInfile();
$cli->addOptionalArg('o')->useAsOutfile();
$cli->addOptionalArg('n')->mustHaveValue();
$cli->addOptionalArg('s')->mustHaveValue();
$cli->addOptionalArg('C');

if (! $cli->validate()) {
    echo $cli->getErrorReport();
    echo "USAGE: ./hexdump.php [-C] [-n NUMBYTES] [-i INFILE] [-o OUTFILE]\n";
    echo "EXAMPLE: echo \"Hello World\" | ./hexdump.php\n";
    exit(0);
}

$outfile = $cli->values['o'];
$infile = $cli->values['i'];
$length = $cli->values['n'];
$start = $cli->values['s'];
$canonical = $cli->values['C'];

$length = is_string($length)
    ? (int)$length
    : false;
$start = is_string($start)
    ? (int)$start
    : false;

// identical handling of streams and files
$in = $cli->openInput();
$out = $cli->openOutput();

$bytesPerLine = 16;

if ($in) {
    $writeLine = function ($out, $startPos, $chars) use ($bytesPerLine, $canonical) {
        $positionLength = $canonical ? 8 : 7;
        $spaceAfterPos = $canonical ? '  ' : ' ';
        $line = sprintf("%0{$positionLength}x{$spaceAfterPos}", $startPos);
        $printables = '';
        for ($i = 0, $l = strlen($chars); $i < $l; $i++) {
            $ord = ord($chars[$i]);
            $line .= sprintf('%02x ', $ord);
            if ($canonical) {
                if ($i == floor($bytesPerLine / 2) - 1) {
                    $line .= ' ';
                }
                $printables .= ($ord >= 32 && $ord <= 126) ? $chars[$i] : '.';
            }
        }
        $line .= str_repeat('   ', ($bytesPerLine - $i));
        if ($canonical && $printables !== '') {
            $line .= " |$printables|";
        }
        fwrite($out, "$line\n");
    };

    $currentByte = 0;
    if (! feof($in)) {
        fread($in, $start); // eat bytes
        $currentByte += $start;
    }
    while (! feof($in)) {
        $chars = fread($in, $bytesPerLine);
        if ($chars === '' || $chars === false) {
            break;
        }
        $charsLen = strlen($chars);
        if ($length === false) {
            $numToUse = $charsLen;
        } else {
            $numToUse = min($charsLen, max(0, $length - $currentByte));
        }
        if ($numToUse) {
            $writeLine($out, $currentByte, substr($chars, 0, $numToUse));
            $currentByte += $numToUse;
        }
    }
    $writeLine($out, $currentByte, '');
}

$cli->closeInput();
$cli->closeOutput();
