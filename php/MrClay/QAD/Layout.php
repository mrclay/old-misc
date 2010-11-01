<?php

/**
 * Layout that renders via calling a function instead of including a script
 */
class MrClay_QAD_Layout extends Zend_Layout {
    protected $__qad_callback_function = null;

    public function setCallback($callback)
    {
        $this->__qad_callback_function = $callback;
        return $this;
    }

    public function render($name = null)
    {
        ob_start();
        call_user_func($this->__qad_callback_function, $this->getView());
        return ob_get_clean();
    }
}