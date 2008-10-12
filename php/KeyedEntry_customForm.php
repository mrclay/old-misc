<?php

require 'KeyedEntry.class.php'; // base class

// extend the class to customize the entry form
class CustomKeyedEntry extends KeyedEntry {
	// your custome send_form function...
	function send_form($nonce) {
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		
		?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Passkey Required</title>
</head>
<body>
<h1>Customized! Passkey Required</h1>
<?php
if (isset($_POST['digest'])) {
	echo '<p><strong>Incorrect. Please re-enter the passkey below.</strong></p>';
} else {
	echo '<p>Please enter a passkey below.</p>';
}
?>
<form action="#" method="post" id="form1">
	<input type="password" id="key" name="key" size="20"
	><input type="submit" name="submit" value="submit">
	<noscript><p><strong style="color:#CC0000">Warning: Javascript is required for key submission.</strong></p></noscript>
</form>
<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post" id="form2">
	<input type="hidden" id="digest" name="digest" value="">
</form>
<script type="text/javascript"><?php echo $this->getFormScript($nonce); ?></script>
</body>
</html><?php
		exit();
	}
}

$cke =& new CustomKeyedEntry(array(
	// available passkeys are 'password' and 'password2'
	'key1' => '5f4dcc3b5aa765d61d8327deb882cf99'
	,'key2' => '6cb75f652a9b52798eb6cf2201057c73'
));

echo '<pre>Resource unlocked. key used = \''.$cke->keyUsed."'</pre>";

?>