<?php
/* cssSwitch style sheet switcher by Steve Clay ~ 2006

1. Set options
2. Place on web
3. Create switch links like: <a href="/cssSwitch.php?css=large.css">large text</a>
4. In pages:
   <?php require 'cssSwitch.php'; ?>
   <link href="/css/<?php echo $cssSwitch_current; ?>" 
         media="screen, projection" type="text/css" rel="stylesheet">
*/

// options

// Default CSS file
$cssSwitch_default = 'small.css';

// alternate CSS files
$cssSwitch_alternates = array('medium.css','large.css');

// where to redirect user if HTTP Referer not sent
$cssSwitch_defaultBounce = '/';

// cookie options
$cssSwitch_cookieName = 'cssSwitch';
$cssSwitch_cookieExpire = time() + 31536000;
$cssSwitch_cookiePath = '/';
$cssSwitch_cookieDomain = '';
$cssSwitch_cookieSecure = false;


if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME'])) {
    // called directly. set or remove cookie
    
	if (in_array($_GET['css'], $cssSwitch_alternates))
    {
        setcookie($cssSwitch_cookieName, $_GET['css'], $cssSwitch_cookieExpire, 
                  $cssSwitch_cookiePath, $cssSwitch_cookieDomain, $cssSwitch_cookieSecure);
    } else {
        // remove cookie
        setcookie($cssSwitch_cookieName, '', time() - 3600, 
                  $cssSwitch_cookiePath, $cssSwitch_cookieDomain, $cssSwitch_cookieSecure);
    }
    _cssSwitch_bounce();
} else {
    // included in page, set $cssSwitch_current
    
    $cssSwitch_current = (
        isset($_COOKIE[$cssSwitch_cookieName])
		&& in_array($_COOKIE[$cssSwitch_cookieName], $cssSwitch_alternates)
    )
        ? $_COOKIE[$cssSwitch_cookieName]
        : $cssSwitch_default;
}

function _cssSwitch_bounce() {
    $bounceTo = (isset($_SERVER['HTTP_REFERER']))?
		$_SERVER['HTTP_REFERER'] : $GLOBALS['cssSwitch_defaultBounce'];
    $htmlBounceTo = htmlspecialchars($bounceTo);
	
    list($software, $version) = explode("/", $_SERVER["SERVER_SOFTWARE"]);
	if ($software == 'Microsoft-IIS' && $version < 6.0) {
		echo <<<EOD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<head><meta http-equiv="Refresh" content="0; URL={$htmlBounceTo}">
<title>Text-size Adjusted</title></head>
<h1>Text-size adjusted.</h1><p>Please <a href='{$htmlBounceTo}'>click here</a>
to return to {$htmlBounceTo} if your browser does not take you there automatically.</p>
EOD;
	} else {
		header("Location: {$bounceTo}");
	}
	exit();
}

