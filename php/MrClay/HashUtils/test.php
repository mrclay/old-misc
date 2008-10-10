<?php

require '../HashUtils.php';

$message = 'Hello World!';
$secretKey = 'byuf5r67g67T6g95gt67T^T^^^$$f434#@43290=mmn';
$realPassword = 'gloworm';
$wrongPassword = 'blowfish';

header('Content-Type: text/plain');

$passwordColumn = MrClay_HashUtils::getSaltedHash($realPassword);
$realIsValid = MrClay_HashUtils::verifyHash($passwordColumn, $realPassword);
$wrongIsValid = MrClay_HashUtils::verifyHash($passwordColumn, $wrongPassword);
echo "Password column: " . var_export($passwordColumn, 1) . "\n";
echo "Real password is valid: " . var_export($realIsValid, 1) . "\n";
echo "Wrong password is valid: " . var_export($wrongIsValid, 1) . "\n\n";

$signedMsg = MrClay_HashUtils::signContent($message, $secretKey);
$msg = MrClay_HashUtils::getContent($signedMsg, $secretKey);
echo "Original message: " . var_export($message, 1) . "\n";
echo "Signed message: " . var_export($signedMsg, 1) . "\n";
echo "Verified message: " . var_export($msg, 1) . "\n\n";

// attempt tampering
$signedMsg[2] = 'L';
$verifiedTamperedMsg = MrClay_HashUtils::getContent($signedMsg, $secretKey);
echo "Tampered signed message: " . var_export($signedMsg, 1) . "\n";
echo "Verified message: " . var_export($verifiedTamperedMsg, 1) . "\n";

