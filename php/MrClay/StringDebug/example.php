<?php

require dirname(__FILE__) . '/../StringDebug.php';

function pre($str) {
    return "<pre>" . htmlspecialchars($str) . "</pre>";
}

$str = "Hello World!\nI\xC3\xB1t\xC3\xABrn\xC3\xA2ti\xC3\xB4n\xC3"
     . "\xA0liz\xC3\xA6ti\xC3\xB8n\rCol1\tCol2\tCol3\r\n\x00C\x01o\x02"
     . "n\x03\x04tr\x05o\x06l\x07 \x08c\x7Fharacters";

header('Content-Type: text/html;charset=utf-8');

echo MrClay_StringDebug::utf8Style()
    ,"<h2>MrClay_StringDebug::highlightUtf8(\$str)</h2>"
    ,MrClay_StringDebug::highlightUtf8($str)
    ,"<h2>MrClay_StringDebug::export(\$str)</h2>"
    ,pre(MrClay_StringDebug::export($str))
    ,"<h2>MrClay_StringDebug::export(\$str, false)</h2>"
    ,pre(MrClay_StringDebug::export($str, false))
    ,"<h2>MrClay_StringDebug::export(\$str, true, 60)</h2>"
    ,pre(MrClay_StringDebug::export($str, true, 60));
