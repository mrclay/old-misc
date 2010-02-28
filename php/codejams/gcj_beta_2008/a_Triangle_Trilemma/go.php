<?php

// Requirements: http://code.google.com/codejam/contest/dashboard?c=32014#s=p0
// UNIX: cat FILE.in | php go.php > FILE.out
//  WIN: php go.php < FILE.in > FILE.out

require 'Point.php';
require 'Triangle.php';

$N = (int)fgets(STDIN);
for ($i = 1; $i <= $N; $i++) {
    $line = trim(fgets(STDIN));
    $c = explode(' ', $line);
    $p1 = new Point($c[0], $c[1]);
    $p2 = new Point($c[2], $c[3]);
    $p3 = new Point($c[4], $c[5]);
    echo "Case #$i: ";
    if ($tri = Triangle::factory($p1, $p2, $p3)) {
        echo $tri->describe();
    } else {
        echo "not a triangle";
    }
    echo "\n";
}
