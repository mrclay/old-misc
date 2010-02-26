<?php

// Requirements: http://code.google.com/codejam/contest/dashboard?c=32003#s=p1
// UNIX: cat FILE.in | php go.php > FILE.out
//  WIN: php go.php < FILE.in > FILE.out

require 'CellCollection.php';
require 'MazeWalker.php';

$N = (int)fgets(STDIN);
for ($i = 1; $i <= $N; $i++) {
    $line = trim(fgets(STDIN));
    list($path1, $path2) = explode(' ', $line);
		
	$maze = new CellCollection();
	$walker = new MazeWalker($maze);
	$walker->explore($path1);
	$walker->explore($path2);
	
	echo "Case #$i:\n";
	$maze->draw();
}
