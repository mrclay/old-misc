<?php

class MrClay_QAD_View_CallbackResolver {
    protected $_prefix = null;
    protected $_callback = null;

    public function __construct($prefix = 'view_')
    {
        $this->_prefix = $prefix;
    }

    public function setCallback($callback)
    {
        $this->_callback = $callback;
    }

    /**
     * Resove a script name to a callback function
     * @param string $script e.g. index/foo-bar.phtml
     * @param Zend_View $view (optional)
     * @return callback e.g. "view_index_foo__bar"
     */
    public function resolve($script, Zend_View $view = null)
    {
        // $view is asked for in case we want to inspect it
        if (null !== $this->_callback) {
            return $this->_callback;
        }
        $script = str_replace('\\', '/', $script);
        $script = $this->_prefix . str_replace('/', '_', $script);
        $script = str_replace('-', '__', $script);
        list($func) = explode('.', $script, 2);
        return $func;
    }
}
