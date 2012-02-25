#!/usr/bin/php
<?php

require dirname(__DIR__) . '/Cli.php';
require __DIR__ . '/Arg.php';

$cli = new MrClay\Cli;
$cli->addRequiredArg('a');
$cli->addOptionalArg('b')->assertFile()->assertReadable();
$cli->addOptionalArg('c')->mayHaveValue();
$cli->addOptionalArg('d');

if (! $cli->validate()) {
    echo $cli->getErrorReport();
    echo "USAGE: ./test.php -a required_value [-b file] [-c [optional_value]] [-d] [file ...]\n";
    echo "Example: ./test.php -a Hello -b test.php\n";
    exit(0);
}

$out = $cli->openOutput();

fwrite($out, var_export(array(
    'values' => $cli->values,
    'more' => $cli->moreArgs,
    'debug' => $cli->debug,
), 1) . "\n");

$cli->closeOutput();
