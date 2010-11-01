<?php

/**
 * View that renders via calling a function instead of including a script.
 * E.g. script name "foo/bar.phtml" might yield the output of function
 * "view_foo_bar".
 */
class MrClay_QAD_View extends Zend_View {
    
    protected $__qad_function_prefix = 'view_';

    public function setFunctionPrefix($prefix = 'view_')
    {
        $this->__qad_function_prefix = $prefix;
        return $this;
    }

    /**
     * Take a view script name and try to get the output from a corresponding
     * view function. 
     *
     * If the function is missing, try regular script rendering.
     *
     * @param string $name The script name to process.
     * @return string output from a function/script
     */
    public function render($name)
    {
        // try function
        $callback = $this->_mapScriptToCallback($name);
        if (function_exists($callback)) {
            return $this->_renderCallback($callback);
        }

        // try file
        // must catch the exception _script() throws if path not found.
        try {
            return parent::render($name);
        } catch (Zend_View_Exception $e) {
            // no script file, carry on
        }
        
        // maybe it's an error
        if ($name === 'error/error.phtml') {
            $callback = array('MrClay_QAD_ErrorController', 'defaultView');
            return $this->_renderCallback($callback);
        }

        // allow all to be silently missing
        return '';
    }

    protected function _renderCallback($callback)
    {
        ob_start();
        call_user_func($callback, $this);
        return ob_get_clean();
    }

    protected function _mapScriptToCallback($script)
    {
        $script = str_replace('\\', '/', $script); // may not be necessary
        $script = $this->__qad_function_prefix . str_replace('/', '_', $script);
        list($func) = explode('.', $script, 2);
        return $func;
    }

    protected function _run()
    {
        include func_get_arg(0);
    }
}