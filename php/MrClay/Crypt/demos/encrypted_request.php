<?php

require __DIR__ . '/../../Loader.php';
MrClay_Loader::getInstance()->register();

function dump($name, $val) { echo "<p><b>" . h($name) . "</b> = <tt>" . h(var_export($val, true)) . "</tt></p>"; }
function h($txt) { return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8'); }

$password = "Just a really bad password";

$msg = array(123, "Lorem ipsum dolor sit amet, consectetur adipiscing elit.");

dump('$msg', $msg);

$er = new \MrClay\Crypt\EncryptedRequest($password);

$encoded = $er->encode($msg);

dump('$encoded', $encoded);

?>
<form action="decrypting_decoder.php" method="GET">
    <p><input type="hidden" name="<?php echo $er->varName; ?>" value="<?php echo h($encoded) ?>">
        <input type="submit" value="decode"></p>
</form>
