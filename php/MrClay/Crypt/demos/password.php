<?php

use MrClay\Crypt\PasswordHasher;
use MrClay\Crypt\KeyDeriver;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

header('Content-Type: text/plain');

$password = 'password1';

$keyDeriver = new KeyDeriver();
$hasher = new PasswordHasher($keyDeriver);

$hash = $hasher->hashPassword($password);

list($valid, $iterations) = $hasher->verifyPassword($password, $hash);

echo "PBKDF2:\n  hash = $hash\n";
echo "  iterations = $iterations\n";
echo "  valid = " . (int)$valid . "\n\n";

$hash = $hasher->hashPassword($password, true);
list($valid, $iterations) = $hasher->verifyPassword($password, $hash);

echo "PBKDF2 timed:\n  hash = $hash\n";
echo "  minTime = " . $keyDeriver->minimumTime . "\n"; 
echo "  iterations = $iterations\n";
echo "  valid = " . (int)$valid . "\n\n";
