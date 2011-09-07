<?php

require dirname(__DIR__) . '/../Rand.php';
require dirname(__DIR__) . '/../Hmac.php';
require dirname(__DIR__) . '/../Hmac/SignedRequest.php';

function h($txt) { return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8'); }

$sr = new MrClay_Hmac_SignedRequest('My big secret!');
$val = array('Hello' => 'World!', 5 => 42);

echo "\$val = " . h(var_export($val, 1));

$encoded = $sr->encode($val);
echo "<br>\$encoded = " . h(var_export($encoded, 1));

?>
<form action="decoder.php" method="GET">
<p><input type="hidden" name="<?php echo $sr->varName; ?>" value="<?php echo h($encoded) ?>">
   <input type="submit" value="decode"></p>
</form>
