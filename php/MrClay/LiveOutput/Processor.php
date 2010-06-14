<?php

class MrClay_LiveOutput_Processor {
    public $title;
    protected $_uniqueVarName;

    public function  __construct($title) {
        $this->title = $title;
        $this->_uniqueVarName = 'lo' . time();
    }

    public function generatePhp($file)
    {
        // if true, functions and classes will be already defined
        $alreadyParsed = in_array(realpath($file), get_included_files());
        
        $tokens = token_get_all(file_get_contents($file));
        $buffer = $evalBuffer = $html = $php = '';
        $bufferingForMethod = ''; // LiveOutput method to be called
        $hasBegun = false;
        $alterNext_T_STRING = false; // is the next T_STRING the name of a function/class?
        
        while (null !== ($token = array_shift($tokens))) { // main loop
            // debug string of tokens
            //$t = $token; if (is_string($t)) echo $t; else var_export(array(token_name($t[0]), $t[1]));

            // don't parse until #!begin is reached
            if (! $hasBegun) {
                if (is_array($token) && ($token[0] === T_COMMENT) && (preg_match('/^#!begin\s*$/', $token[1]))) {
                    $hasBegun = true;
                } else {
                    continue;
                }
            }

            if (is_string($token)) {
                $buffer     .= $token;
                $evalBuffer .= $token;
            } else {
                // named token
                $tokenTxt = $token[1];
                switch ($token[0]) {
                    case T_COMMENT:
                        if (0 === strpos($tokenTxt, '/*!')) {
                            $html .= trim(preg_replace('@(^/\*!|\n\s*\*+|/\\s*$)@', '', $tokenTxt));
                            // don't append buffers
                            continue 2;
                        }
                        if (preg_match('/^#!(html|desc)\s(.+)$/', $tokenTxt, $m)) {
                            if ($m[1] === 'html') {
                                $php .= "\${$this->_uniqueVarName}->renderer->rawHtml(" 
                                      . var_export($m[2], 1) . ");\n";
                            } else {
                                $html .= "<p>" . htmlentities(trim($m[2]), ENT_QUOTES, 'UTF-8') . "</p>";
                            }
                            // don't append buffers
                            continue 2;
                        }
                        if (preg_match('/^#!(reset|code(?:Return)?(?:Render)?)?\s*$/', $tokenTxt, $m)) {
                            // processor comment
                            if ($bufferingForMethod) {
                                // buffer was collecting for a function
                                $php .= $this->_processSection($bufferingForMethod, $buffer, $evalBuffer, $html);
                                $html = '';
                            } else {
                                // copy code as is
                                $php .= $evalBuffer . "\n";
                            }
                            $buffer = $evalBuffer = '';
                            if (isset($m[1])) {
                                if ($m[1] === 'reset') {
                                    $php = '';
                                    $bufferingForMethod = '';
                                    continue 2;
                                } elseif (0 === strpos($m[1], 'code')) {
                                    // start capturing for function call
                                    $bufferingForMethod = $m[1];
                                }
                            } else {
                                $bufferingForMethod = '';
                            }
                            // don't append buffers
                            continue 2;
                        } else {
                            if ($bufferingForMethod) {
                                $buffer .= $tokenTxt;
                            }
                            // no point in adding to eval buffer
                            continue 2;
                        }
                        break;
                    case T_STRING:
                        if ($alterNext_T_STRING) {
                            // this is a class/function name. it was declared at parse
                            // time so we must mangle the name in our eval code.
                            $buffer     .= $tokenTxt;
                            $evalBuffer .= "{$this->_uniqueVarName}{$tokenTxt}";
                            $alterNext_T_STRING = false;
                            // don't append buffers
                            continue 2;
                        }
                        break;
                    case T_OPEN_TAG:
                    case T_CLOSE_TAG:
                        // don't append buffers
                        continue 2;
                    case T_INLINE_HTML:
                        $html .= trim($tokenTxt);
                        // don't append buffers
                        continue 2;
                    case T_FUNCTION:
                    case T_CLASS:
                        if ($alreadyParsed) {
                            // functions/classes were defined at parse time, so we'll
                            // have to hack the names of functions/classes to avoid
                            // naming conflicts. (This is much easier than removing
                            // them.)
                            $alterNext_T_STRING = true;
                        }
                    default:
                } // switch
                $buffer     .= $tokenTxt;
                $evalBuffer .= $tokenTxt;
            } // named token
        } // main loop

        if ($bufferingForMethod) {
            // buffer was collecting for a function
            $php .= $this->_processSection($bufferingForMethod, $buffer, $evalBuffer, $html);
        }

        $php = "\${$this->_uniqueVarName}=new MrClay_LiveOutput("
              . var_export($this->title, 1) . ");\n" . $php;

        // debug
        //highlight_string("<?php\n$php"); die();
        //die($php);
        
        return $php;
    }

    protected function _processSection($liveOutputMethod, $displayCode, $evalCode, $html)
    {
        $beforeCall = ($html !== '')
            ? "\${$this->_uniqueVarName}->htmlForNextBlock(" . var_export($html, 1) . ");\n"
            : '';
        $codeStr = var_export(trim($displayCode), 1);
        $phpExpression = rtrim($evalCode, " \t\r\n\0\x0B;");
        if ($liveOutputMethod == 'code') {
            $argumentList = $codeStr;
            $afterCall = "\n$phpExpression;\n";
        } else {
            $argumentList = "$codeStr, $phpExpression";
            $afterCall = "\n";
        }
        $call = "\${$this->_uniqueVarName}->$liveOutputMethod($argumentList);";
        return "{$beforeCall}{$call}{$afterCall}";
    }
}