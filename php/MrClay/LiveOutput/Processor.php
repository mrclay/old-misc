<?php

class MrClay_LiveOutput_Processor {
    public $title;
    protected $_loVar;

    public function  __construct($title) {
        $this->title = $title;
        $this->_loVar = 'lo' . time();
    }

    public function process($file)
    {
        $tokens = token_get_all(file_get_contents($file));
        $buff = $mangledBuff = '';
        $code = "\${$this->_loVar}=new MrClay_LiveOutput(" . var_export($this->title, 1) . ");\n";
        $func = false; // function to be called
        $mangleNextString = false; // is the next T_STRING the name of a function/class?
        while (null !== ($t = array_shift($tokens))) {
            // debug
            //if (is_string($t)) echo $t; else var_export(array(token_name($t[0]), $t[1]));

            if (is_string($t)) {
                $buff .= $t;
                $mangledBuff .= $t;
            } else {
                // named token
                if ($t[0] === T_COMMENT && (preg_match('@^\#\!(begin|code(?:Return)?(?:Render)?)?\s+$@', $t[1], $m))) {
                    // special comment
                    if ($func) {
                        // buffer was collecting for a function
                        $code .= $this->_writeCall($func, $buff, $mangledBuff);
                    }
                    $func = (isset($m[1]) && 0 === strpos($m[1], 'code'))
                        ? $m[1]
                        : false;
                    $buff = $mangledBuff = '';
                } else {
                    if ($t[0] === T_STRING && $mangleNextString) {
                        $buff .= $t[1];
                        $mangledBuff .= "{$this->_loVar}{$t[1]}";
                        $mangleNextString = false;
                        continue;
                    }
                    if ($t[0] === T_OPEN_TAG || $t[0] === T_CLOSE_TAG) {
                        continue;
                    }
                    if ($t[0] === T_FUNCTION || $t[0] === T_CLASS) {
                        // since functions/classes are defined at parse time, and this
                        // file was already parsed, we hack the function/class name during
                        // eval to ensure no conflicts. This is much easier than removing
                        // them.
                        $mangleNextString = true;
                    }
                    if ($t[0] === T_INLINE_HTML) {
                        $code .= "\${$this->_loVar}->html(" . var_export(trim($t[1]), 1) . ");\n";
                        $buff = $mangledBuff = '';
                        continue;
                    }
                    $buff .= $t[1];
                    $mangledBuff .= $t[1];
                }
            }
        }
        if ($func) {
            // buffer was collecting for a function
            $code .= $this->_writeCall($func, $buff, $mangledBuff);
        }

        // debug
        //highlight_string("<?php\n$code"); die();
        //echo $code;

        eval($code); // cross fingers
        exit();
    }

    protected function _writeCall($func, $code, $mangledCode)
    {
        $codeStr = var_export(trim($code), 1);
        $codeExpr = rtrim($mangledCode, " \t\r\n\0\x0B;");
        return ($func == 'code')
            ? "\${$this->_loVar}->$func($codeStr);\n$codeExpr\n;\n"
            : "\${$this->_loVar}->$func($codeStr, $codeExpr);\n";
    }

}