<?php 

namespace MrClay;

/**
 * Handle and validate command-line arguments (options)
 *
 * A bit hacked together, but useful.
 */
class CliArgs {
    
    /**
     * @var array validation errors
     */
    public $errors = array();
    
    /**
     * @var array option values available after validation.
     * 
     * E.g. array(
     *      'a' => false              // option was missing
     *     ,'b' => true               // option was present
     *     ,'c' => "Hello"            // option had value
     *     ,'f' => "/home/user/file"  // file path from root
     *     ,'f.raw' => "~/file"       // file path as given to option
     * )
     */
    public $values = array();

    public $debug = array();

    protected $_args = array();
    protected $_stdin = null;
    protected $_stdout = null;
    
    /**
     * @param bool $exitIfNoStdin (default true) Exit() if STDIN is not defined
     */
    function __construct($exitIfNoStdin = true) 
    {
        if ($exitIfNoStdin && ! defined('STDIN')) {
            exit('This script is for command-line use only.');
        }
    }
    
    /**
     * Add a letter an option
     * 
     * @param string $letter
     * 
     * @param array $spec (optional) specification of the option
     * 
     * By default, the option will be an optional flag with no value following.
     * 
     * If 'isRequired', the option (but not necessarily a value) will be required.
     * 
     * If 'canHaveValue' the option may accept a following value if present.
     * 
     * If 'mustHaveValue' (or 'isDir' or 'isFile'), the option MUST be followed by a value to validate.
     * 
     * If 'isDir' or 'isFile', during validation the value will be converted to a full file path (not
     * necessarily existing!) and the original value will be accessible via a "*.raw" key. 
     * E.g. $ca->values['f.raw']
     * 
     * If 'isReadble', a file value will be tested with is_readable during validation.
     * 
     * If 'isWritable', the value (or the parent directory if the file doesn't exist) will be tested
     * with is_writable during validation.
     * 
     * If 'STDIN' and the option is present, its value must be a readable file to validate.
     * 
     * If 'STDOUT' and the option is present, its value must be a writable file or directory to validate.
     * 
     * @return null
     */
    function addArgument($letter, $spec = array())
    {
        $defaults = array(
             'isRequired' => false
            ,'canHaveValue' => false
            ,'mustHaveValue' => false
            ,'isFile' => false
            ,'isDir' => false
            ,'isReadable' => false
            ,'isWritable' => false
            ,'STDIN' => false
        );
        $spec = array_merge($defaults, $spec);
        if ($spec['STDIN']) {
            $spec['isFile']     = true;
            $spec['isReadable'] = true;
        }
        if ($spec['STDOUT']) {
            $spec['isFile']     = true;
            $spec['isWritable'] = true;
        }
        if ($spec['isFile'] || $spec['isDir']) {
            $spec['mustHaveValue'] = true;
        }
        $this->_args[$letter] = $spec;
    }
    
    /*
     * Read and validate options
     * 
     * @return bool true if all options are valid
     */
    function validate()
    {
        $options = '';
        $this->errors = array();
        $this->values = array();
        $this->_stdin = null;
        
        if (isset($GLOBALS['argv'][1]) 
            && ($GLOBALS['argv'][1] === '-?'
                || $GLOBALS['argv'][1] === '--help'
                )) {
            return false;
        }
        
        $lettersUsed = '';
        foreach ($this->_args as $letter => $spec) {
            $options .= $letter;
            $lettersUsed .= $letter;
            
            if ($spec['canHaveValue'] || $spec['mustHaveValue']) {
                $options .= ($spec['mustHaveValue'] ? ':' : '::');
            }
        }

        $argvCopy = $GLOBALS['argv'];
        $o = getopt($options);

        $this->debug['_getopt_options'] = $options;
        $this->debug['_getopt_return'] = $o;

        foreach ($o as $letter => $value) {
            while ($k = array_search("-" . $letter, $argvCopy)) {
                if ($k) {
                    unset($argvCopy[$k]);
                }
                if (preg_match("/^.*" . $letter . ":.*$/i", $options)) {
                    unset($argvCopy[$k + 1]);
                }
            }
        }
        $argvCopy = array_slice($argvCopy, 1);

        foreach ($this->_args as $letter => $spec) {
            $this->values[$letter] = false;
            if (isset($o[$letter])) {
                if (is_bool($o[$letter])) {
                    if ($spec['mustHaveValue']) {
                        $this->errors[$letter][] = "Missing value";
                    } else {
                        $this->values[$letter] = true;
                    }
                } else {
                    // string
                    $this->values[$letter] = $o[$letter];
                    $v =& $this->values[$letter];

                    // remove from argvCopy
                    $pattern = "/^-{$letter}=?" . preg_quote($v, '/') . "$/";
                    foreach ($argvCopy as $argK => $arg) {
                        if (preg_match($pattern, $arg)) {
                            unset($argvCopy[$argK]);
                        }
                    }
                    
                    // check that value isn't really another option
                    if (strlen($lettersUsed) > 1) {
                        $pattern = "/^-[" . str_replace($letter, '', $lettersUsed) . "]/i";
                        if (preg_match($pattern, $v)) {
                            $this->errors[$letter][] = "Value was read as another option: " . var_export($v, 1);
                            return false;
                        }    
                    }
                    if ($spec['isFile'] || $spec['isDir']) {
                        if ($v[0] !== '/' && $v[0] !== '~') {
                            $this->values["$letter.raw"] = $v;
                            $v = getcwd() . "/$v";
                        }
                    }
                    if ($spec['isFile']) {
                        if ($spec['STDIN']) {
                            $this->_stdin = $v;
                        } elseif ($spec['STDOUT']) {
                            $this->_stdout = $v;
                        }
                        if ($spec['isReadable'] && ! is_readable($v)) {
                            $this->errors[$letter][] = "File not readable: " . var_export($v, 1);
                            continue;
                        }
                        if ($spec['isWritable']) {
                            if (is_file($v)) {
                                if (! is_writable($v)) {
                                    $this->errors[$letter][] = "File not writable: " . var_export($v, 1);
                                }
                            } else {
                                if (! is_writable(dirname($v))) {
                                    $this->errors[$letter][] = "Directory not writable: " . var_export(dirname($v), 1);
                                }
                            }
                        }
                    } elseif ($spec['isDir'] && $spec['isWritable'] && ! is_writable($v)) {
                        $this->errors[$letter][] = "Directory not readable: " . var_export($v, 1);
                    }
                }
            } else {
                if ($spec['isRequired']) {
                    $this->errors[$letter][] = "Missing";
                }
            }
        }
        $this->values['more'] = $argvCopy;
        return empty($this->errors);
    }
    
    /**
     * Get a short list of errors with options
     * 
     * @return string
     */
    function getErrors()
    {
        if (empty($this->errors)) {
            return '';
        }
        $r = "Problems with your options:\n";
        foreach ($this->errors as $letter => $arr) {
            $r .= "  $letter : " . implode(', ', $arr) . "\n";
        }
        $r .= "\n";
        return $r;
    }
    
    /**
     * Get resource of open input stream. May be STDIN or a file pointer
     * to the file specified by an option with 'STDIN'.
     *
     * @return resource
     */
    function openInput()
    {
        if (null === $this->_stdin) {
            return STDIN;
        } else {
            $this->_stdin = fopen($this->_stdin, 'rb');
            return $this->_stdin;
        }
    }
    
    function closeInput()
    {
        if (null !== $this->_stdin) {
            fclose($this->_stdin);
        }
    }
    
    /**
     * Get resource of open output stream. May be STDOUT or a file pointer
     * to the file specified by an option with 'STDOUT'.
     *
     * @return resource
     */
    function openOutput()
    {
        if (null === $this->_stdout) {
            return STDOUT;
        } else {
            $this->_stdout = fopen($this->_stdout, 'wb');
            return $this->_stdout;
        }
    }
    
    function closeOutput()
    {
        if (null !== $this->_stdout) {
            fclose($this->_stdout);
        }
    }
}

