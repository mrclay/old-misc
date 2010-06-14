<?php

#!begin

#!code
/*! <p>Strings can contain all kinds of wacky bytes.</p>
 */
$str = "Hello World!\nI\xC3\xB1t\xC3\xABrn\xC3\xA2ti\xC3\xB4n\xC3"
     . "\xA0liz\xC3\xA6ti\xC3\xB8n\rCol1\tCol2\tCol3\r\n\x00C\x01o\x02"
     . "n\x03\x04tr\x05o\x06l\x07 \x08c\x7Fharacters";

#!code
/*! <p><a href="http://code.google.com/p/mrclay/source/browse/trunk/php/MrClay/StringDebug.php">
 * StringDebug</a> makes it easier to grok them.</p>
 */
require_once '../../StringDebug.php';


#!codeRender
/*! <p><code>highlightUtf8()</code> takes a string of valid (and maybe non-valid)
 *  UTF-8 bytes and renders it such that non-ASCII/non-printable codepoints are
 * highlighted and linked to a Unicode reference site.</p>
 */
MrClay_StringDebug::highlightUtf8($str);
// STYLE element provided by MrClay_StringDebug::utf8Style().
#!

echo MrClay_StringDebug::utf8Style();

#!codeReturn
/*! <p><code>export()</code> returns a more readable PHP string. Like
 * <code>var_export($str, 1)</code>, it's valid PHP code.</p> <p>When your string
 * may not be valid in <em>any</em> encoding, this may be easier than dealing
 * with a hex editor.</p>
 */
MrClay_StringDebug::export($str);
// note the return value is shown double-quoted below.

#!codeReturn
// don't escape upper ASCII bytes
MrClay_StringDebug::export($str, false);

