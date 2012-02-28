<?php

/**
 * "Pretty Print" a JSON string
 *
 * Based on http://www.php.net/manual/en/function.json-encode.php#80339
 *
 * To alter formatting, change $tab and/or extend the class and override
 * the protected methods
 *
 * <code>
 * $formatter = new MrClay_JsonFormatter();
 * echo $formatter->format($json);
 * </code>
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_JsonFormatter {

    public $tab = "  ";

    protected $_indentLevel = 0;

    protected function _openObject()
    {
        $ret = "{\n" . str_repeat($this->tab, $this->_indentLevel + 1);
        $this->_indentLevel++;
        return $ret;
    }

    protected function _openArray()
    {
        $ret = "[\n" . str_repeat($this->tab, $this->_indentLevel + 1);
        $this->_indentLevel++;
        return $ret;
    }

    protected function _closeObject()
    {
        $this->_indentLevel--;
        return "\n" . str_repeat($this->tab, $this->_indentLevel) . "}";
    }

    protected function _closeArray()
    {
        $this->_indentLevel--;
        return "\n" . str_repeat($this->tab, $this->_indentLevel) . "]";
    }

    protected function _comma()
    {
        return ",\n" . str_repeat($this->tab, $this->_indentLevel);
    }

    protected function _colon()
    {
        return " : ";
    }

    protected function _string($str)
    {
        return "\"$str\"";
    }

    public function format($json) {
        $this->_indentLevel = 0;
        $output = '';
        $len = strlen($json);

        $jsonIdx = 0;
        while ($jsonIdx < $len) {
            $char = $json[$jsonIdx];

            if ($char === '"') {
                // copy string contents to $str
                $i = $jsonIdx + 1;
                $str = '';
                while (true) {
                    if ($json[$i] === "\\" && $json[$i+1] === '"') {
                        // copy as is
                        $str .= '\\"';
                        $i += 2;
                    } elseif ($json[$i] === '"') {
                        // end string
                        $i++;
                        break;
                    } else {
                        // copy string char
                        $str .= $json[$i];
                        $i++;
                    }
                }
                $jsonIdx = $i;
                $output .= $this->_string($str);
                continue;
            }

            switch($char) {
                case '{':
                    $output .= $this->_openObject();
                    break;
                case '[':
                    $output .= $this->_openArray();
                    break;
                case '}':
                    $output .= $this->_closeObject();
                    break;
                case ']':
                    $output .= $this->_closeArray();
                    break;
                case ',':
                    $output .= $this->_comma();
                    break;
                case ':':
                    $output .= $this->_colon();
                    break;
                default:
                    // number, true, false, or null
                    $output .= $char;
                    break;
            }
            $jsonIdx++;
        }
        return $output;
    }
}
