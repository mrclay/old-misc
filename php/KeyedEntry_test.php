<?php

require 'KeyedEntry.class.php';
$ke =& new KeyedEntry(
	array( // keys
		// available passkeys are 'password' and 'password2'
		'guest' => '5f4dcc3b5aa765d61d8327deb882cf99'
		,'admin' => md5('password2')
	),
	array( // options
		'resourceTitle' => 'test page'
	)
);

// the following are optional, and you can call them eg. based on
// $ke->keyUsed, so some keys may get stricter session limits.

if ('guest' === $ke->keyUsed) {
    $ke->session_time_limit(30);
    $ke->expire_after_last_visit(10);
}

echo "<pre>Resource unlocked. 
If you are 'guest' your session will expire in 30 seconds
and you must reload at least every 10 seconds to stay logged in.

\$key-&gt;keyUsed = '{$ke->keyUsed}'
\$key-&gt;loginTime = {$ke->loginTime}
\$key-&gt;lastVisitTime = {$ke->lastVisitTime}
</pre>";

?>