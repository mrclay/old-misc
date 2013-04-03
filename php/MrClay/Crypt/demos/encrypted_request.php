<?php

use MrClay\Crypt\Encryption;
use MrClay\Crypt\EncryptedRequest;
use MrClay\Crypt\Encoding\Base64Url;

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

function dump($name, $val) { echo "<pre><b>" . h($name) . "</b> = " . h(var_export($val, true)) . "</pre>"; }
function h($txt) { return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8'); }

$encoding = new Base64Url();
$key = $encoding->decode('TPwFGDfoaw-cLulL_WCE4RUHATWz9AOx3mnmjxv-5Ls');

$msg = array(123, "Lorem ipsum dolor sit amet, consectetur adipiscing elit.");

dump('$msg', $msg);

$er = new EncryptedRequest(new Encryption($key));

$encoded = $er->encode($msg);

dump('$encoded', $encoded);

?>
<form action="decrypting_decoder.php" method="GET">
    <p><input type="hidden" name="<?php echo $er->varName; ?>" value="<?php echo h($encoded) ?>">
        <input type="submit" value="decode"></p>
</form>
