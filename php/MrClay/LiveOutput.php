<?php

/**
 * Create a page demonstrating the output of PHP code. Given blocks of PHP code are
 * highlighted and methods allow displaying the return value (rendered with var_export and
 * syntax highlighted) and/or an HTML rendering.
 * 
 * The Good: Output is real-time so documentation never lies.
 * 
 * The Bad: Code has to appear twice in script (once in string, 2nd as argument) since
 * eval() modifies scope. It may be possible to implement this design using special
 * comments, then reading the file from disk to get the source.
 * 
 * The Ugly: Not an excuse for lack of unit tests, pretty limited documentation use.
 * 
 * Extend and override display() and/or displaySections() to alter markup/charset.
 * 
 * @todo Output sections as executed rather than storing return values
 * @todo Linked code blocks
 * 
 * Designed ~2005, minor PHP5 cleanup 2008-10-12
 */
class MrClay_LiveOutput {

    public $title = '';
    
    public function __construct($title = '')
    {
        $this->title = $title;
    }
    
    public function codeRender($code, $return)
    {
        return $this->_addBlock($code, $return, false, true);
    }
    
    public function codeReturnRender($code, $return)
    {
        return $this->_addBlock($code, $return, true, true);
    }
    
    public function codeReturn($code, $return)
    {
        return $this->_addBlock($code, $return, true, false);
    }
    
    public function code($code, $return = true)
    {
        return $this->_addBlock($code, $return, false, false);
    }
    
    public function displaySections()
    {
        $toggle = true;
        foreach ($this->_blocks as $block) {
            echo "<div class='section'>\n"
                ,"<div class='code'>\n"
                ,self::_highlight($block['code'])
                ,"\n</div>";
            if ($block['showReturn']) {
                echo "\n<h3>returns</h3><div class='produces'>\n"
                    ,self::_highlight(var_export($block['return'], 1))
                    ,"</div>";
            }
            if ($block['render'] && is_string($block['return'])) {
                echo "\n<h3>rendering</h3>\n<div class='rendering'>\n"
                    ,$block['return']
                    ,"\n</div>\n";
            }
            echo "\n</div>\n";
        }
    }

    public function display()
    {
        header('Content-Type: text/html; charset=utf-8');
        
        ?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
"http://www.w3.org/TR/html4/loose.dtd">
<head><title>Live output: <?php echo htmlspecialchars($this->title); ?></title>
<style type="text/css">
code {font-size:86%}
.code {padding:10px; background-color:#ffd;}
.section {padding:0 15px 15px; border:1px #fff solid; border-bottom:5px #000 solid; background:#eee}
.rendering {padding:10px; background-color:#fff;}
.produces {padding:10px; max-height:150px; overflow:auto;}
h2, h3 {margin:10px 0}
h1 small {color:#999;}
</style>
</head>
<h1><small>Live output:</small> <?php echo htmlspecialchars($this->title); ?></h1>
<?php echo $this->displaySections();
    
    }

    private function _addBlock($code, $return, $showReturn, $render)
    {
        $this->_blocks[] = array(
            'code' => trim($code)
            ,'return' => $return
            ,'showReturn' => $showReturn
            ,'render' => $render
        );
        return $return;
    }
    
    protected static function _highlight($php)
    {
        $h = highlight_string("<?php {$php} ?>", 1);
        $h = str_replace('<span style="color: #0000BB">&lt;?php&nbsp;','<span style="color: #0000BB">', $h);
        $h = str_replace('<br />\'&nbsp;</span>', '\'</span>', $h);
        $h = str_replace('?&gt;', '', $h);
        $h = preg_replace('@<span [^>]*></span>@', '', $h);
        $h = preg_replace('@ #([A-F\\d])\\1([A-F\\d])\\2([A-F\\d])\\3"@', '#$1$2$3"', $h);
        return $h;
    }
    
    protected $_blocks = array();
}

