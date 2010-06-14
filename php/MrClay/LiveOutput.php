<?php

/**
 * Create a page demonstrating the output of PHP code. Given blocks of PHP code are
 * highlighted and methods allow displaying the return value (rendered with var_export and
 * syntax highlighted) and/or an HTML rendering.
 * 
 * The Good: Output is real-time so documentation never lies.
 * 
 * The Bad: Not an excuse for lack of unit tests, pretty limited documentation use.
 * 
 * @todo Linked code blocks
 * 
 * Designed ~2005, minor PHP5 cleanup 2008-10-12,
 * added renderer and processor 2010-06-14
 */
class MrClay_LiveOutput {

    public $title = '';
    public $renderer = null;
    protected $_ob = false;
    
    public function __construct($title = '', MrClay_LiveOutput_Renderer $renderer = null)
    {
        $this->title = $title;
        if (! $renderer) {
            $renderer = new MrClay_LiveOutput_Renderer($title);
        }
        $this->renderer = $renderer;
    }
    
    public function codeRender($code, $return)
    {
        $this->renderBlock($code, $return, false, true);
    }
    
    public function codeReturnRender($code, $return)
    {
        $this->renderBlock($code, $return, true, true);
    }
    
    public function codeReturn($code, $return)
    {
        $this->renderBlock($code, $return, true, false);
    }
    
    public function code($code, $return = true)
    {
        $this->renderBlock($code, $return, false, false);
    }

    public function renderBlock($code, $return, $showReturn = false, $render = false)
    {
        if ($this->_ob) {
            $this->renderer->html(ob_get_clean());
            $this->_ob = false;
        }
        $this->renderer->block($code, $return, $showReturn, $render);
    }

    public function ob_start()
    {
        ob_start();
        $this->_ob = true;
    }

    public function html($html)
    {
        $this->renderer->html($html);
    }

    public static function processThis($title)
    {
        $stack = debug_backtrace();
        $processor = new MrClay_LiveOutput_Processor($title);
        $processor->process($stack[0]['file']);
    }
}
