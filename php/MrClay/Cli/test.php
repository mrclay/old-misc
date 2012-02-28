#!/usr/bin/php
<?php

require dirname(__DIR__) . '/Cli.php';
require __DIR__ . '/Arg.php';

$cli = new MrClay\Cli;
$cli->addRequiredArg('a')->setDescription('A required argument');
$cli->addOptionalArg('b')->assertFile()->assertReadable()->setDescription('If set, you must provide a valid file path');
$cli->addOptionalArg('c')->mayHaveValue()->setDescription('Can be set with or without a value');
$cli->addOptionalArg('d')->setDescription('Optional flag');

if (! $cli->validate()) {
    echo $cli->getErrorReport();
    echo "USAGE: ./test.php -a required_value [-b file] [-c [optional_value]] [-d] [file ...]\n";
    echo "Example: ./test.php -a Hello -b test.php\n";
    if ($cli->isHelpRequest) {
        echo $cli->getArgumentsListing();
    }
    exit(0);
}

$out = $cli->openOutput();

fwrite($out, var_export(array(
    'values' => $cli->values,
    'more' => $cli->moreArgs,
    'debug' => $cli->debug,
), 1) . "\n");

$cli->closeOutput();
