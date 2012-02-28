<?php

/** 
 * Utilities for debugging strings in a more readable way
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_StringDebug {
    
    /**
     * Get a more human-readable double-quoted PHP string usable in PHP source
     * code.
     * 
     * Common control characters (including 127/7F) are always escaped and 8-bit
     * bytes are escaped by default.
     * 
     * @param string $str a string
     * 
     * @param bool $escapeUpper128 (default true) escape bytes with values above 127. If set to
     * false, bytes with values above 127 will be preserved. E.g. UTF-8 or other
     * supersets of US-ASCII will be left as is. By default the resulting string
     * will be "7-bit clean"
     * 
     * @param int $limit (default 0) if given, the returned string will be split
     * with a linefeed character as not to exceed this many characters. 
     * 
     * @return string PHP double-quoted string
     * 
     * @link http://php.net/manual/en/language.types.string.php#language.types.string.syntax.double
     */
    public static function export($str, $escapeUpper128 = true, $limit = 0)
    {
        $ret = '"';
        $len = 1;
        for ($i = 0, $l = strlen($str); $i < $l; ++$i) {
            $o = ord($str[$i]);
            if ($o < 31 || $o == 127) {
                switch ($o) {
                    case 9: self::_a('\t', $ret, $len); break;
                    case 10: self::_a('\n', $ret, $len); break;
                    case 13: self::_a('\r', $ret, $len); break;
                    default: 
                        $ret .= '\x' . str_pad(strtoupper(dechex($o)), 2, '0', STR_PAD_LEFT);
                        $len += 4;
                        
                }
            } elseif ($o > 127) {
                if ($escapeUpper128) {
                    $ret .= '\x' . strtoupper(dechex($o));
                    $len += 4;
                } else {
                    $ret .= $str[$i];
                    $len += 1; 
                }
            } else {
                switch ($o) {
                    case 36: self::_a('\$', $ret, $len); break;
                    case 34: self::_a('\"', $ret, $len); break;
                    case 92: self::_a('\\\\', $ret, $len); break;
                    default: 
                        $ret .= $str[$i];
                        $len += 1;
                }
            }
            if ($limit && $len > ($limit - 5)) {
                $ret .= "\"\n. \"";
                $len = 1;
            }
        }
        return $ret . '"';
    }
    
    /**
     * Get an (X)HTML rendering of a UTF-8 string with all non-printable US-ASCII
     * characters linked to their Unicode code points.
     * 
     * @param string $str UTF-8 encoded string
     * 
     * @param bool $xhtml (default false) should output be XHTML?
     * 
     * @return string (X)HTML markup
     */
    public static function highlightUtf8($str, $xhtml = false)
    {
        $br = $xhtml ? '<br />' : '<br>';
        $ret = '<pre class="utf8_debug">';
        $mbChar = ''; 
        for ($i = 0, $l = strlen($str); $i < $l; ++$i) {
            $o = ord($str[$i]);
            if ($mbChar !== '') {
                if ($o <= 127 || ($o >= 194 && $o <= 244)) {
                    // start of new char
                    $hex = strtoupper(dechex(self::_utf8_ord($mbChar)));
                    $hex = str_pad(
                        $hex
                        ,(strlen($hex) > 4 ? 6 : 4)
                        ,'0'
                        ,STR_PAD_LEFT
                    );
                    $ret .= self::_char($mbChar, $hex);
                    // @todo: append annotation
                    $mbChar = '';
                } else {
                    // add byte
                    $mbChar .= $str[$i];
                    continue;
                }
            }
            if (13 == $o && ($i + 1) < $l && $str[$i + 1] === "\n") {
                $ret .= self::_char('\\r', '000D');
            } elseif ($o < 31 || 127 == $o) {
                switch ($o) {
                    case 0: $ret .= self::_char('\\0', '0000'); break;
                    case 9: $ret .= self::_char('\\t', '0009'); break;
                    case 10: $ret .= self::_char('\\n', '000A') . $br; break;
                    case 13: $ret .= self::_char('\\r', '000D') . $br; break;
                    default: 
                        $hex = str_pad(strtoupper(dechex($o)), 2, '0', STR_PAD_LEFT);
                        $ret .= self::_char("\\x{$hex}", "00{$hex}");
                }
            } elseif ($o > 127) {
                $mbChar = $str[$i];
            } else {
                switch ($o) {
                    case 32: $ret .= self::_char(' ', '0020'); break;
                    case 38: $ret .= '&amp;'; break;
                    case 60: $ret .= '&lt;'; break;
                    case 62: $ret .= '&gt;'; break;
                    default: $ret .= $str[$i];
                }
            }
        }
        return $ret . '</pre>';
    }
    
    /**
     * Get a STYLE element to render output of MrClay_StringDebug::highlightUtf8()
     * 
     * The parent PRE is rendered white on black.
     * Codepoints above 127 include a grey dotted border.
     * Tab is 4 characters wide with a green dotted border.
     * Newline chars have a red dotted border.
     * ASCII spaces have only a bottom border. 
     * Non-whitespace control characters are rendered black on grey.
     * 
     * @return string (X)HTML markup
     */
    public static function utf8Style()
    {
        return "<style type=\"text/css\">
.utf8_debug {background:#000; color:#fff; padding:3px 1px 2px; line-height:1.7;}
.utf8_debug a {background:#000; color:#fff; border:1px dotted #ccc; margin:0 1px; padding:0 1px; text-decoration:none;}
/* ctrl chars */ .utf8_debug .ctrl {background:#ccc; color:#000; border:0; margin:0 1px; padding:0 1px;}
/* whitespace */
.utf8_debug .cp_0009,
.utf8_debug .cp_0020,
.utf8_debug .cp_000A,
.utf8_debug .cp_000D {border:1px dotted #faa; background:#000; color:#faa;}
/* EOL chars */
.utf8_debug .cp_0009,
.utf8_debug .cp_0020 {border-color:#afa; color:#afa;}
/* space */ .utf8_debug .cp_0020 {border-width:0 0 1px}
/* tab */ .utf8_debug .cp_0009 {padding-right:1.5em;}
</style>";
    }
    
    private static function _a($str, &$str1, &$len)
    {
        $str1 .= $str;
        $len += strlen($str);
    }
    
    private static function _char($content, $codePoint)
    {
        $class = ('007F' === $codePoint || hexdec($codePoint) < 32)
            ? " class='cp_{$codePoint} ctrl'"
            : " class='cp_{$codePoint}'";
        return "<a{$class} href='http://www.fileformat.info/info/unicode"
             . "/char/{$codePoint}/index.htm' title='U+{$codePoint}"
             . "'>{$content}</a>";
    }
    
    private static function _utf8_ord($chr) 
    {
        $ord0 = ord($chr);
        if ( $ord0 >= 0 && $ord0 <= 127 ) {
            return $ord0;
        }
        if ( !isset($chr{1}) ) {
            trigger_error('Short sequence - at least 2 bytes expected, only 1 seen');
            return FALSE;
        }
        $ord1 = ord($chr{1});
        if ( $ord0 >= 192 && $ord0 <= 223 ) {
            return ( $ord0 - 192 ) * 64 
                + ( $ord1 - 128 );
        }
        if ( !isset($chr{2}) ) {
            trigger_error('Short sequence - at least 3 bytes expected, only 2 seen');
            return FALSE;
        }
        $ord2 = ord($chr{2});
        if ( $ord0 >= 224 && $ord0 <= 239 ) {
            return ($ord0-224)*4096 
                + ($ord1-128)*64 
                    + ($ord2-128);
        }
        if ( !isset($chr{3}) ) {
            trigger_error('Short sequence - at least 4 bytes expected, only 3 seen');
            return FALSE;
        }
        $ord3 = ord($chr{3});
        if ($ord0>=240 && $ord0<=247) {
            return ($ord0-240)*262144 
                + ($ord1-128)*4096 
                    + ($ord2-128)*64 
                        + ($ord3-128);
        
        }
        if ( !isset($chr{4}) ) {
            trigger_error('Short sequence - at least 5 bytes expected, only 4 seen');
            return FALSE;
        }
        $ord4 = ord($chr{4});
        if ($ord0>=248 && $ord0<=251) {
            return ($ord0-248)*16777216 
                + ($ord1-128)*262144 
                    + ($ord2-128)*4096 
                        + ($ord3-128)*64 
                            + ($ord4-128);
        }
        if ( !isset($chr{5}) ) {
            trigger_error('Short sequence - at least 6 bytes expected, only 5 seen');
            return FALSE;
        }
        if ($ord0>=252 && $ord0<=253) {
            return ($ord0-252) * 1073741824 
                + ($ord1-128)*16777216 
                    + ($ord2-128)*262144 
                        + ($ord3-128)*4096 
                            + ($ord4-128)*64 
                                + (ord($chr{5})-128);
        }
        if ( $ord0 >= 254 && $ord0 <= 255 ) { 
            trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0);
            return FALSE;
        }
    }
}
