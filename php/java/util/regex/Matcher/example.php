<?php

header('Content-Type: text/plain; charset=utf-8');

use java\util\regex\Pattern;

require dirname(__DIR__) . '/Pattern.php';

foreach (array(
    'numbered' => 'the ((?:red|white) (king|queen))',
    'named' => '(?<date>(?<year>(\d\d)?\d\d)-(?<month>\d\d)-(?<day>\d\d))',
    'duplicate names' => '(?J:(?<DN>Mon|Fri|Sun)(?:day)?|(?<DN>Tue)(?:sday)?|(?<DN>Wed)(?:nesday)?|(?<DN>Thu)(?:rsday)?|(?<DN>Sat)(?:urday)?)',
    'duplicate numbers' => '(?|(Sat)ur|(Sun))day',
    ) as $type => $regex) {
    $pattern = new Pattern($regex, 0);
    echo var_export($pattern->capturingGroupCount(), 1) . "\n";
}