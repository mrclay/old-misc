<?php

// Requirements: http://code.google.com/codejam/contest/dashboard?c=32003
// UNIX: cat A-large-practice.in | php alien_numbers.php > A-large-practice.out
//  WIN: php alien_numbers.php < A-large-practice.in > A-large-practice.out

require 'AlienUnsignedInt.php';

$N = (int)fgets(STDIN);
for ($i = 1; $i <= $N; $i++) {
    $line = trim(fgets(STDIN));
    list($number, $sourceDigits, $targetDigits) = explode(' ', $line);
    $oldNum = new AlienUnsignedInt($number, $sourceDigits);
    $newNum = $oldNum->convert($targetDigits);
    echo "Case #$i: $newNum\n";
}
